<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ledger of every money movement on an investment (initial amount, top-ups
     * added later on the same invoice, and the return), each linked to the bank
     * account transaction it posted. Before this, investments were tied to the
     * bank only by note text, so edits and deletes left the bank out of step.
     */
    public function up()
    {
        Schema::create('investment_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('investment_id');
            $table->string('type', 20); // investment | top_up | return
            $table->decimal('amount', 22, 4)->default(0);
            $table->date('transaction_date')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('account_transaction_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('investment_id')->references('id')->on('investments')->onDelete('cascade');
            $table->index(['investment_id', 'type']);
            $table->index('account_transaction_id');
        });

        $this->backfill();
    }

    public function down()
    {
        Schema::dropIfExists('investment_transactions');
    }

    /**
     * One entry per existing investment (and one for its return, if any),
     * linked to the bank transaction it most likely created. Amounts are taken
     * from the investment; bank amounts are left untouched so any existing
     * mismatch shows up in the Investments page "Check Bank Mismatch" report.
     */
    protected function backfill()
    {
        $names = DB::table('investors')->pluck('name', 'id');
        $used = [];
        $now = now();

        foreach (DB::table('investments')->orderBy('id')->get() as $inv) {
            $name = $names[$inv->investor_id] ?? '';

            $deposit_at = null;
            if (! empty($inv->received_account_id)) {
                $deposit_at = $this->match($inv->received_account_id, 'credit', 'Investment deposit:', $name,
                    $inv->received_date, (float) $inv->amount, $inv->created_at, $used);
            }

            DB::table('investment_transactions')->insert([
                'investment_id' => $inv->id,
                'type' => 'investment',
                'amount' => $inv->amount,
                'transaction_date' => $inv->received_date,
                'account_id' => $inv->received_account_id,
                'account_transaction_id' => $deposit_at,
                'created_by' => $inv->created_by,
                'created_at' => $inv->created_at ?? $now,
                'updated_at' => $now,
            ]);

            if ((float) $inv->return_amount > 0) {
                $return_at = null;
                if (! empty($inv->return_account_id)) {
                    // The return debit was written when the return was last saved,
                    // which is also when the investment row was last updated.
                    $return_at = $this->match($inv->return_account_id, 'debit', 'Investment return:', $name,
                        $inv->return_date, (float) $inv->return_amount, $inv->updated_at, $used);
                }

                DB::table('investment_transactions')->insert([
                    'investment_id' => $inv->id,
                    'type' => 'return',
                    'amount' => $inv->return_amount,
                    'transaction_date' => $inv->return_date,
                    'account_id' => $inv->return_account_id,
                    'account_transaction_id' => $return_at,
                    'created_by' => $inv->created_by,
                    'created_at' => $inv->updated_at ?? $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Find the unlinked bank transaction an investment wrote. Strongest signal
     * first: written within two minutes of the anchor time (same request), then
     * exact note + date + amount, then exact note + date, then note + amount.
     */
    protected function match($account_id, $type, $prefix, $name, $date, $amount, $anchor, array &$used)
    {
        $candidates = DB::table('account_transactions')
            ->whereNull('deleted_at')
            ->where('account_id', $account_id)
            ->where('type', $type)
            ->where('note', 'like', $prefix.'%')
            ->when(! empty($used), function ($q) use ($used) {
                $q->whereNotIn('id', $used);
            })
            ->orderBy('id', 'desc')
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        $exact_note = $prefix.' '.$name;
        $same_date = function ($c) use ($date) {
            return $date && substr((string) $c->operation_date, 0, 10) === substr((string) $date, 0, 10);
        };
        $same_amount = function ($c) use ($amount) {
            return abs((float) $c->amount - $amount) < 0.005;
        };

        $pick = null;

        if ($anchor) {
            $anchor_ts = strtotime($anchor);
            $closest = $candidates->filter(function ($c) {
                return ! empty($c->created_at);
            })->sortBy(function ($c) use ($anchor_ts) {
                return abs(strtotime($c->created_at) - $anchor_ts);
            })->first();

            if ($closest && abs(strtotime($closest->created_at) - $anchor_ts) <= 120) {
                $pick = $closest;
            }
        }

        $by_note = $candidates->where('note', $exact_note);
        $pick = $pick
            ?? $by_note->first(function ($c) use ($same_date, $same_amount) {
                return $same_date($c) && $same_amount($c);
            })
            ?? $by_note->first($same_date)
            ?? $by_note->first($same_amount);

        if (! $pick) {
            return null;
        }

        $used[] = $pick->id;

        return $pick->id;
    }
};
