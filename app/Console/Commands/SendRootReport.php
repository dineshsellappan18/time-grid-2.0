<?php

namespace App\Console\Commands;

use App\TG\TransMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendRootReport extends Command
{
    protected $signature = 'root:report';

    protected $description = 'Send Root Email Report';

    public function __construct(
        private TransMail $transmail,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        logger()->info('Generating Root Report');

        $registeredUsersCount = DB::table('users')->count();

        logger()->info('Users Count: '.$registeredUsersCount);

        $params = [
            'registeredUsersCount' => $registeredUsersCount,
        ];
        $header = [
            'name'  => 'Root',
            'email' => config('root.report.to_mail'),
        ];
        $this->transmail->template('root.report.report')
                        ->subject('root.report.subject')
                        ->send($header, $params);

        $this->info('Root report was sent');

        return 0;
    }
}
