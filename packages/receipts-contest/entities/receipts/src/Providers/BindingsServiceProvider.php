<?php

namespace Packages\ReceiptsContest\Receipts\Providers;

use Illuminate\Contracts\Foundation\Application;
use InetStudio\ReceiptsContest\Receipts\Providers\BindingsServiceProvider as PackageBindingsServiceProvider;

final class BindingsServiceProvider extends PackageBindingsServiceProvider
{
    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->bindings['InetStudio\ReceiptsContest\Receipts\Contracts\Console\Commands\ModerateCommandContract'] = 'Packages\ReceiptsContest\Receipts\Console\Commands\ModerateCommand';
        $this->bindings['InetStudio\ReceiptsContest\Receipts\Contracts\Console\Commands\SetWinnerCommandContract'] = 'Packages\ReceiptsContest\Receipts\Console\Commands\SetWinnerCommand';
        $this->bindings['InetStudio\ReceiptsContest\Receipts\Http\Requests\Front\SendRequest'] = 'Packages\ReceiptsContest\Receipts\Http\Requests\Front\SendRequest';
        $this->bindings['InetStudio\ReceiptsContest\Receipts\Contracts\Services\Front\ItemsServiceContract'] = 'Packages\ReceiptsContest\Receipts\Services\Front\ItemsService';
    }
}
