<?php

class Payment extends Model {
    protected $table = 'payments';

    protected $id;
    public $protocol;
    public $payment_method;
    public $amount;
    public $created_at;
    public $updated_at;
    public $deleted_at;
}
