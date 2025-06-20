<?php

declare(strict_types=1);

namespace BetaKiller\Model;

use InvalidArgumentException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Spotman\Defence\Parameter\ArgumentParameterInterface;
use Spotman\Defence\Parameter\ArgumentParameterNameTrait;

final readonly class Phone implements ArgumentParameterInterface
{
    use ArgumentParameterNameTrait;

    private PhoneNumberUtil $utils;
    private PhoneNumber $number;

    public static function fromDb(string $value): self
    {
        return new self('+'.$value);
    }

    public static function fromUserInput(string $input): self
    {
        // Remove non-number characters
        $input = preg_replace('/\D/', '', $input);

        return self::fromDb($input);
    }

    private function __construct(string $phone)
    {
        $this->utils = PhoneNumberUtil::getInstance();

        try {
            $this->number = $this->utils->parse($phone, 'RU'); // Always RU for now
        } catch (NumberParseException $e) {
            throw new InvalidArgumentException(sprintf('Wrong phone number (%s): "%s"', $e->getMessage(), $phone));
        }
    }

    public function formatted(): string
    {
        return $this->utils->format($this->number, PhoneNumberFormat::INTERNATIONAL);
    }

    public function dbValue(): string
    {
        return trim($this->e164(), '+');
    }

    public function e164(): string
    {
        return $this->utils->format($this->number, PhoneNumberFormat::E164);
    }

    public function link(): string
    {
        return $this->utils->format($this->number, PhoneNumberFormat::RFC3966);
    }

    public function isEqualTo(Phone $to): bool
    {
        return $this->e164() === $to->e164();
    }
}
