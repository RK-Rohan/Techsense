<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;

class AccountingAccountType extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function getAccountTypeNameAttribute()
    {
        $name = ! empty($this->business_id) ? $this->name : __('accounting::lang.'.$this->name);

        return $name;
    }

    public function getAccountTypeDescriptionAttribute()
    {
        if (empty($this->descriptiion)) {
            return '';
        }

        $descriptiion = ! empty($this->business_id) ?
        $this->descriptiion : __('accounting::lang.'.$this->descriptiion);

        return $descriptiion;
    }

    /**
     * Get the parent of the type
     */
    public function parent()
    {
        return $this->belongsTo('Modules\Accounting\Entities\AccountingAccountType', 'parent_id', 'id');
    }

    public static function usesOnlyAccrualChart($business_id)
    {
        $groups = static::where('business_id', $business_id)->whereNotNull('chart_code');
        if (! $groups->exists()) {
            return false;
        }

        return ! AccountingAccount::where('business_id', $business_id)
            ->where(function ($query) use ($groups) {
                $query->whereNull('account_sub_type_id')
                    ->orWhereNotIn('account_sub_type_id', $groups->select('id'));
            })->exists();
    }

    public function scopeForBusiness($query, $business_id)
    {
        if (static::usesOnlyAccrualChart($business_id)) {
            return $query->where('business_id', $business_id)
                ->where(function ($query) use ($business_id) {
                    $query->whereNotNull('chart_code')
                        ->orWhereIn('parent_id', static::where('business_id', $business_id)->whereNotNull('chart_code')->select('id'));
                });
        }

        return $query->where(function ($query) use ($business_id) {
            $query->whereNull('business_id')->orWhere('business_id', $business_id);
        });
    }

    public static function accounting_primary_type($business_id = null)
    {
        $accounting_primary_type = [
            'asset' => ['label' => __('accounting::lang.asset')],
            'expenses' => ['label' => __('accounting::lang.expenses')],
            'income' => ['label' => __('accounting::lang.income')],
            'equity' => ['label' => __('accounting::lang.equity')],
            'liability' => ['label' => __('accounting::lang.liability')],
        ];

        foreach (['revenue', 'cost_of_goods_sold', 'non_operating_income', 'finance_costs'] as $type) {
            $accounting_primary_type[$type] = ['label' => __('accounting::lang.'.$type)];
        }

        if ($business_id !== null && static::usesOnlyAccrualChart($business_id)) {
            return array_map(function ($class) {
                return ['label' => $class[0]];
            }, config('accounting.accrual_chart'));
        }

        return $accounting_primary_type;
    }
}
