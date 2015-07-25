<?php ( defined('SYSPATH') ) OR die('No direct script access.');


abstract class CRM_Import_Abstract_Load_Sheet extends CRM_Import_Abstract_Sheet {

    const DEBUG = FALSE;

    public function set_e(CRM_Import_Abstract_Errors $errors)
    {
        $this->e = $errors;
    }

    protected function debug_order($order, $die = FALSE)
    {
        /** @var $rate_obj Model_Rate */
        $rate_obj = $order->rates;
        $rates = $rate_obj->find_all()->as_array(NULL, 'name');

        echo '<ul>';
        echo '<li>id = '.$order->id.'</li>';
        echo '<li>phone_home = '.$order->phone_home.'</li>';
        echo '<li>status = '.$this->s->label($order->status).'</li>';
        echo '<li>date_delivery = '.$order->date_delivery.'</li>';
        echo '<li>comment = '.$order->comment.'</li>';

        echo '<li>тарифы: '.implode(', ', $rates).'</li>';
        // print_r($order);
        echo '</ul><br /><br />';

        if ( $die ) die();
    }
}

class CRM_Import_Abstract_Load_Sheet_Exception extends CRM_Import_Abstract_Sheet_Exception {}