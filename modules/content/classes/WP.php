<?php

class WP
{
    public const POST_TYPE_PAGE       = 'page';
    public const POST_TYPE_POST       = 'post';
    public const POST_TYPE_REVISION   = 'revision';
    public const POST_TYPE_ATTACHMENT = 'attachment';

    /**
     * @param string|array            $postTypes
     * @param \DateTimeImmutable|NULL $skipBefore
     *
     * @return array
     */
    public function getPostsByTypes($postTypes, DateTimeImmutable $skipBefore = null): array
    {
        if (!is_array($postTypes)) {
            $postTypes = [$postTypes];
        }

        $query = DB::select()
            ->from('posts')
            ->and_where('post_type', 'IN', $postTypes)
            ->and_where('post_status', '=', 'publish');

        if ($skipBefore) {
            $filterValue = $skipBefore->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            $query->and_where('post_date_gmt', '>=', $filterValue);
        }

        return $query->execute('wp')->as_array();
    }

    /**
     * @param \DateTimeImmutable|null $skipBefore
     *
     * @return array
     */
    public function getPostsAndPages(DateTimeImmutable $skipBefore = null): array
    {
        return $this->getPostsByTypes([self::POST_TYPE_PAGE, self::POST_TYPE_POST], $skipBefore);
    }

    /**
     * @param array|null $mimeTypes
     * @param array|null $filterIDs
     *
     * @return array
     */
    public function getAttachments(array $mimeTypes = null, array $filterIDs = null): array
    {
        $query = DB::select()
            ->from('posts')
            ->and_where('post_type', '=', self::POST_TYPE_ATTACHMENT);

        if ($mimeTypes) {
            $query->and_where('post_mime_type', 'IN', $mimeTypes);
        }

        if ($filterIDs) {
            $query->where('ID', 'IN', $filterIDs);
        }

        return $query->execute('wp')->as_array();
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getAttachmentByID(int $id): array
    {
        $query = DB::select()
            ->from('posts')
            ->and_where('post_type', '=', self::POST_TYPE_ATTACHMENT)
            ->and_where('ID', '=', $id)
            ->limit(1);

        /** @var Database_Result $result */
        $result = $query->execute('wp');

        return (array)$result->current();
    }

    /**
     * @param string $path
     *
     * @return array
     */
    public function getAttachmentByPath(string $path): array
    {
        $query = DB::select()
            ->from('posts')
            ->and_where('post_type', '=', self::POST_TYPE_ATTACHMENT)
            ->and_where('guid', 'LIKE', '%'.$path)
            ->limit(1);

        /** @var Database_Result $result */
        $result = $query->execute('wp');

        return (array)$result->current();
    }

    /**
     * @param int $postID
     *
     * @return array
     */
    public function getPostMeta(int $postID): array
    {
        $meta = DB::select('meta_key', 'meta_value')
            ->from('postmeta')
            ->where('post_id', '=', $postID)
            ->execute('wp');

        $output = [];

        foreach ($meta as $row) {
            $output[$row['meta_key']] = $row['meta_value'];
        }

        return $output;
    }

    public function postHasTerms(int $postID, $terms, $taxonomy = null): bool
    {
        $query = DB::select()
            ->from('term_relationships')
            ->join('term_taxonomy')->on('term_taxonomy.term_id', '=', 'term_relationships.term_taxonomy_id')
            ->join('terms')->on('terms.term_id', '=', 'term_taxonomy.term_id')
            ->and_where('term_relationships.object_id', '=', $postID)
            ->and_where('terms.slug', 'IN', (array)$terms);

        if ($taxonomy) {
            $query->and_where('term_taxonomy.taxonomy', '=', $taxonomy);
        }

        /** @var Database_Result $result */
        $result = $query->execute('wp');

        return ($result->count() > 0);
    }

    public function postHasPostFormat(int $postID, string $format): bool
    {
        return $this->postHasTerms($postID, 'post-format-'.$format, 'post_format');
    }

    /**
     * @return array
     */
    public function getCategoriesWithPosts(): array
    {
        /** @var Database_Result $query */
        $query = DB::select_array([
            'terms.term_id',
            'terms.name',
            'terms.slug',
            'term_taxonomy.parent',
        ])
            ->from('term_taxonomy')
            ->join('terms', 'left')
            ->on('terms.term_id', '=', 'term_taxonomy.term_id')
            ->join('term_relationships', 'left')
            ->on('term_relationships.term_taxonomy_id', '=', 'term_taxonomy.term_taxonomy_id')
            ->where('term_taxonomy.taxonomy', '=', 'category')
            ->group_by('term_taxonomy.term_id')
            ->execute('wp');

        /** SELECT wp_terms.term_id, wp_terms.name, wp_terms.slug, wp_term_taxonomy.parent, COUNT(wp_term_relationships.object_id) as posts_count
         * FROM wp_term_taxonomy
         * LEFT JOIN wp_terms ON wp_terms.term_id = wp_term_taxonomy.term_id
         * LEFT JOIN wp_term_relationships ON wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id
         * WHERE wp_term_taxonomy.taxonomy = 'category'
         * GROUP BY wp_term_taxonomy.term_id
         * HAVING posts_count > 0
         * ORDER BY parent */

        return $query->as_array();
    }

    /**
     * @param int $termID
     *
     * @return int[]
     */
    public function getPostsIDsLinkedToCategory(int $termID): array
    {
        /** @var Database_Result $query */
        $query = DB::select_array([
            [DB::expr('GROUP_CONCAT(wp_term_relationships.object_id SEPARATOR ",")'), 'post_ids'],
        ])
            ->from('term_relationships')
//            ->join('terms', 'left')
//            ->on('terms.term_id', '=', 'term_taxonomy.term_id')

            ->join('term_taxonomy', 'left')
            ->on('term_taxonomy.term_taxonomy_id', '=', 'term_relationships.term_taxonomy_id')
            ->where('term_taxonomy.term_id', '=', $termID)
            ->group_by('term_taxonomy.term_id')
            ->execute('wp');

        $postIDs = $query->get('post_ids');

        return $postIDs ? explode(',', $postIDs) : [];
    }

    public function getOption(string $name, ?string $default = null): ?string
    {
        /** @var Database_Result $query */
        $query = DB::select()
            ->from('options')
            ->and_where('option_name', '=', $name)
            ->limit(1)
            ->execute('wp');

        return (string)$query->get('option_value') ?: $default;
    }

    public function setOption(string $name, string $value): void
    {
        try {
            DB::insert('options', ['option_name', 'option_value'])
                ->values([
                    'option_name'  => $name,
                    'option_value' => $value,
                ])
                ->execute('wp');
        } /** @noinspection BadExceptionsProcessingInspection */
        catch (Database_Exception $e) {
            DB::update('options')
                ->set([
                    'option_value' => $value,
                ])
                ->where('option_name', '=', $name)
                ->execute('wp');
        }
    }

    /**
     * @return array
     */
    public function getComments(): array
    {
        /** @var Database_Result $query */
        $query = DB::select_array([
            ['comment_ID', 'id'],
            ['comment_parent', 'parent_id'],
            ['comment_post_ID', 'post_id'],
            ['comment_date', 'created_at'],
            ['comment_author', 'author_name'],
            ['comment_author_email', 'author_email'],
            ['comment_author_IP', 'author_ip_address'],
            ['comment_content', 'message'],
            ['comment_approved', 'approved'],
            ['comment_agent', 'user_agent'],
            ['user_id', 'user_id'],
        ])
            ->from('comments')
            ->execute('wp');

        return $query->as_array();
    }

    /**
     * @return array
     */
    public function getUsers(): array
    {
        /** @var Database_Result $query */
        $query = DB::select_array([
            ['ID', 'id'],
            ['user_login', 'login'],
            ['user_email', 'email'],
        ])
            ->from('users')
            ->execute('wp');

        return $query->as_array();
    }

    /**
     * @return array
     */
    public function getQuotesCollectionQuotes(): array
    {
        /** @var Database_Result $query */
        $query = DB::select_array([
            ['quote_id', 'id'],
            ['quote', 'text'],
            'author',
            ['time_added', 'created_at'],
        ])
            ->from('quotescollection')
            ->execute('wp');

        return $query->as_array();
    }

    /**
     * @param int $id
     *
     * @return array[]
     * @throws \BetaKiller\Exception
     */
    public function getWonderpluginSliderConfig(int $id): array
    {
        /** @var Database_Result $query */
        $query = DB::select()
            ->from('wonderplugin_slider')
            ->and_where('id', '=', $id)
            ->limit(1)
            ->execute('wp');

        if (!$query->count()) {
            throw new BetaKiller\Exception('Can not find wonderplugin config for id = :id', [':id' => $id]);
        }

        return json_decode($query->get('data'), true);
    }

    /**
     * Replaces double line-breaks with paragraph elements.
     *
     * A group of regex replaces used to identify text formatted with newlines and
     * replace double line-breaks with HTML paragraph tags. The remaining line-breaks
     * after conversion become <<br />> tags, unless $br is set to '0' or 'false'.
     *
     * @since 0.71
     *
     * @param string $pee The text which has to be formatted.
     * @param bool   $br  Optional. If set, this will convert all remaining line-breaks
     *                    after paragraphing. Default true.
     *
     * @return string Text which has been converted into correct paragraph tags.
     */
    public function autop(?string $pee, bool $br = true)
    {
        $pre_tags = [];

        if (trim($pee) === '') {
            return '';
        }

        // Just to make things a little easier, pad the end.
        $pee .= "\n";

        /*
         * Pre tags shouldn't be touched by autop.
         * Replace pre tags with placeholders and bring them back after autop.
         */
        if (strpos($pee, '<pre') !== false) {
            $pee_parts = explode('</pre>', $pee);
            $last_pee  = array_pop($pee_parts);
            $pee       = '';
            $i         = 0;

            foreach ($pee_parts as $pee_part) {
                $start = strpos($pee_part, '<pre');

                // Malformed html?
                if ($start === false) {
                    $pee .= $pee_part;
                    continue;
                }

                $name            = "<pre wp-pre-tag-$i></pre>";
                $pre_tags[$name] = substr($pee_part, $start).'</pre>';

                $pee .= substr($pee_part, 0, $start).$name;
                $i++;
            }

            $pee .= $last_pee;
        }
        // Change multiple <br>s into two line breaks, which will turn into paragraphs.
        $pee = preg_replace('|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee);

        $allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

        // Add a double line break above block-level opening tags.
        $pee = preg_replace('!(<'.$allblocks.'[\s/>])!', "\n\n$1", $pee);

        // Add a double line break below block-level closing tags.
        $pee = preg_replace('!(</'.$allblocks.'>)!', "$1\n\n", $pee);

        // Standardize newline characters to "\n".
        $pee = str_replace(["\r\n", "\r"], "\n", $pee);

        // Find newlines in all elements and add placeholders.
        $pee = $this->replaceInHtmlTags($pee, ["\n" => " <!-- wpnl --> "]);

        // Collapse line breaks before and after <option> elements so they don't get autop'd.
        if (strpos($pee, '<option') !== false) {
            $pee = preg_replace('|\s*<option|', '<option', $pee);
            $pee = preg_replace('|</option>\s*|', '</option>', $pee);
        }

        /*
         * Collapse line breaks inside <object> elements, before <param> and <embed> elements
         * so they don't get autop'd.
         */
        if (strpos($pee, '</object>') !== false) {
            $pee = preg_replace('|(<object[^>]*>)\s*|', '$1', $pee);
            $pee = preg_replace('|\s*</object>|', '</object>', $pee);
            $pee = preg_replace('%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $pee);
        }

        /*
         * Collapse line breaks inside <audio> and <video> elements,
         * before and after <source> and <track> elements.
         */
        if (strpos($pee, '<source') !== false || strpos($pee, '<track') !== false) {
            $pee = preg_replace('%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $pee);
            $pee = preg_replace('%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $pee);
            $pee = preg_replace('%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $pee);
        }

        // Remove more than two contiguous line breaks.
        $pee = preg_replace("/\n\n+/", "\n\n", $pee);

        // Split up the contents into an array of strings, separated by double line breaks.
        $pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);

        // Reset $pee prior to rebuilding.
        $pee = '';

        // Rebuild the content as a string, wrapping every bit with a <p>.
        foreach ($pees as $tinkle) {
            $pee .= '<p>'.trim($tinkle, "\n")."</p>\n";
        }

        // Under certain strange conditions it could create a P of entirely whitespace.
        $pee = preg_replace('|<p>\s*</p>|', '', $pee);

        // Add a closing <p> inside <div>, <address>, or <form> tag if missing.
        $pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee);

        // If an opening or closing block element tag is wrapped in a <p>, unwrap it.
        $pee = preg_replace('!<p>\s*(</?'.$allblocks.'[^>]*>)\s*</p>!', "$1", $pee);

        // In some cases <li> may get wrapped in <p>, fix them.
        $pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee);

        // If a <blockquote> is wrapped with a <p>, move it inside the <blockquote>.
        $pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
        $pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);

        // If an opening or closing block element tag is preceded by an opening <p> tag, remove it.
        $pee = preg_replace('!<p>\s*(</?'.$allblocks.'[^>]*>)!', "$1", $pee);

        // If an opening or closing block element tag is followed by a closing <p> tag, remove it.
        $pee = preg_replace('!(</?'.$allblocks.'[^>]*>)\s*</p>!', "$1", $pee);

        // Optionally insert line breaks.
        if ($br) {
            // Replace newlines that shouldn't be touched with a placeholder.
            $pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', [$this, 'autopNewlinePreservationHelper'],
                $pee);

            // Normalize <br>
            $pee = str_replace(['<br>', '<br/>'], '<br />', $pee);

            // Replace any new line characters that aren't preceded by a <br /> with a <br />.
            $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee);

            // Replace newline placeholders with newlines.
            $pee = str_replace('<WPPreserveNewline />', "\n", $pee);
        }

        // If a <br /> tag is after an opening or closing block tag, remove it.
        $pee = preg_replace('!(</?'.$allblocks.'[^>]*>)\s*<br />!', "$1", $pee);

        // If a <br /> tag is before a subset of opening or closing block tags, remove it.
        $pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
        $pee = preg_replace("|\n</p>$|", '</p>', $pee);

        // Replace placeholder <pre> tags with their original content.
        if (!empty($pre_tags)) {
            $pee = str_replace(array_keys($pre_tags), array_values($pre_tags), $pee);
        }

        // Restore newlines in all elements.
        if (false !== strpos($pee, '<!-- wpnl -->')) {
            $pee = str_replace([' <!-- wpnl --> ', '<!-- wpnl -->'], "\n", $pee);
        }

        return $pee;
    }

    /**
     * Replace characters or phrases within HTML elements only.
     *
     * @since 4.2.3
     *
     * @param string $haystack      The text which has to be formatted.
     * @param array  $replace_pairs In the form array('from' => 'to', ...).
     *
     * @return string The formatted text.
     */
    private function replaceInHtmlTags($haystack, $replace_pairs)
    {
        // Find all elements.
        $textarr = $this->htmlSplit($haystack);
        $changed = false;

        // Optimize when searching for one item.
        if (1 === count($replace_pairs)) {
            // Extract $needle and $replace.
            foreach ($replace_pairs as $needle => $replace) {
                ;
            }

            // Loop through delimiters (elements) only.
            for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
                if (false !== strpos($textarr[$i], $needle)) {
                    $textarr[$i] = str_replace($needle, $replace, $textarr[$i]);
                    $changed     = true;
                }
            }
        } else {
            // Extract all $needles.
            $needles = array_keys($replace_pairs);

            // Loop through delimiters (elements) only.
            for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
                foreach ($needles as $needle) {
                    if (false !== strpos($textarr[$i], $needle)) {
                        $textarr[$i] = strtr($textarr[$i], $replace_pairs);
                        $changed     = true;
                        // After one strtr() break out of the foreach loop and look at next element.
                        break;
                    }
                }
            }
        }

        if ($changed) {
            $haystack = implode($textarr);
        }

        return $haystack;
    }

    /**
     * Separate HTML elements and comments from the text.
     *
     * @since 4.2.4
     *
     * @param string $input The text which has to be formatted.
     *
     * @return array The formatted text.
     */
    private function htmlSplit(string $input): array
    {
        return preg_split($this->getHtmlSplitRegex(), $input, -1, PREG_SPLIT_DELIM_CAPTURE);
    }

    /**
     * Retrieve the regular expression for an HTML element.
     *
     * @since 4.4.0
     *
     * @return string The regular expression
     */
    private function getHtmlSplitRegex(): string
    {
        static $regex;

        if (!isset($regex)) {
            $comments =
                '!'           // Start of comment, after the <.
                .'(?:'         // Unroll the loop: Consume everything until --> is found.
                .'-(?!->)' // Dash not followed by end of comment.
                .'[^\-]*+' // Consume non-dashes.
                .')*+'         // Loop possessively.
                .'(?:-->)?';   // End of comment. If not found, match all input.

            $cdata =
                '!\[CDATA\['  // Start of comment, after the <.
                .'[^\]]*+'     // Consume non-].
                .'(?:'         // Unroll the loop: Consume everything until ]]> is found.
                .'](?!]>)' // One ] not followed by end of comment.
                .'[^\]]*+' // Consume non-].
                .')*+'         // Loop possessively.
                .'(?:]]>)?';   // End of comment. If not found, match all input.

            $escaped =
                '(?='           // Is the element escaped?
                .'!--'
                .'|'
                .'!\[CDATA\['
                .')'
                .'(?(?=!-)'      // If yes, which type?
                .$comments
                .'|'
                .$cdata
                .')';

            $regex =
                '/('              // Capture the entire match.
                .'<'           // Find start of element.
                .'(?'          // Conditional expression follows.
                .$escaped  // Find end of escaped element.
                .'|'           // ... else ...
                .'[^>]*>?' // Find end of normal element.
                .')'
                .')/';
        }

        return $regex;
    }

    /**
     * Newline preservation help function for wpautop
     *
     * @since  3.1.0
     * @access private
     *
     * @param array $matches preg_replace_callback matches array
     *
     * @return string
     */
    private function autopNewlinePreservationHelper(array $matches): string
    {
        return str_replace("\n", '<WPPreserveNewline />', $matches[0]);
    }
}
