<?php

declare(strict_types=1);

namespace App\States\SalesOrder;

use App\States\SalesOrder\Cancel;
use App\States\SalesOrder\Completed;
use App\States\SalesOrder\Pending;
use App\States\SalesOrder\Progress;
use App\States\SalesOrder\Transitions\PendingToCancel;
use App\States\SalesOrder\Transitions\PendingToProgress;
use App\States\SalesOrder\Transitions\ProgressToCompleted;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

// abstract itu contract campur abstract classnya
abstract class SalesOrderState extends State
{
    abstract public function label() : string;

    public static function config() : StateConfig
    {
        // biar lebih ke dokumentasi untuk state perubahannya
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Progress::class, PendingToProgress::class)
            ->allowTransition(Pending::class, Cancel::class, PendingToCancel::class)
            ->allowTransition(Progress::class, Completed::class, ProgressToCompleted::class);
    }
}