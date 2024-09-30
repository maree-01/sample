<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GstinData extends Model
{
    use HasFactory;

    protected $table ='tbl_gst';

    protected $fillable = [
        'gstin',
        'trade_name',
        'registration_date',
        'status',
        'address',
        'state',
        'district',
        'pincode',
        'legal_name',
        'business_type',
        'last_updated',
        'e_invoice_status',
        'created_at',
        'updated_at'
    ];
}
