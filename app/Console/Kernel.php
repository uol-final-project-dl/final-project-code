<?php /** @noinspection SpellCheckingInspection */

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Playground::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     * @SuppressWarnings ("php:S1192")
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('tasks:message-empty-rooms')->everyMinute();
        $schedule->command('tasks:email-last-messages')->hourly();
        $schedule->command('tasks:message-hosts')->everyFiveMinutes();
        $schedule->command('tasks:save-kpis')->daily();
        $schedule->command('tasks:send-reminders')->dailyAt('15:00');

        $schedule->command('tasks:solve-old-chats')->dailyAt('6:00');

        $schedule->command('tasks:create-company-activities')->dailyAt('3:00');

        $schedule->command('tasks:send-event-reminder')->dailyAt('16:00');
        $schedule->command('tasks:send-event-push-notifications')->hourlyAt(10);

        $schedule->command('tasks:message-group-hosts')->everyThirtyMinutes();
        $schedule->command('tasks:message-group-users')->dailyAt('19:00');

        $schedule->command('tasks:send-announcement-notification')->everyFiveMinutes();
        $schedule->command('tasks:manage-recurring-tasks')->weekly();
        $schedule->command('tasks:message-unaccepted-group-users')->dailyAt('11:00');

        $schedule->command('tasks:process-template-general-rules')->dailyAt('9:30');
        $schedule->command('tasks:process-template-process-task-triggers-daily')->dailyAt('10:00');

        $schedule->command('tasks:process-template-process-task-rules')
            ->everyThirtySeconds()
            ->between('7:00', '22:00');
        $schedule->command('tasks:process-template-process-task-triggers')
            ->everyThirtyMinutes();
        $schedule->command('tasks:send-host-notifications')->dailyAt('9:00');

        $schedule->command('tasks:send-host-weekly-emails')->weeklyOn(1, '6:00');
        $schedule->command('tasks:send-host-weekly-emails')->weeklyOn(5, '6:00');

        $schedule->command('tasks:send-hospitality-weekly-emails')->weeklyOn(5, '12:00');

        $schedule->command('tasks:send-company-weekly-emails')->weeklyOn(1, '12:00');

        $schedule->command('tasks:export-user-services-to-sheet')->cron('0 10,13,16 * * *');

        $schedule->command('tasks:update-wefact-invoices')->everyFifteenMinutes();
        $schedule->command('tasks:update-all-wefact-invoices')->daily();

        $schedule->command('tasks:reset-ooo-status')->daily();

        $schedule->command('tasks:update-user-end-dates')->dailyAt('23:00');

        $schedule->command('tasks:send-reminder-initiations')->dailyAt('9:05');

        $schedule->command('tasks:process-settly-general-rules --type=normal')->dailyAt('10:00');
        $schedule->command('tasks:process-settly-general-rules --type=arrival')->hourly();

        $schedule->command('tasks:send-scheduled-mass-chat')->everyFifteenMinutes();

        $schedule->command('tasks:import-typeform-responses')->daily();
        $schedule->command('tasks:update-user-reporting-status')->daily();

        $schedule->command('shift:send-host-reminder')->hourly();

        $schedule->command('tasks:send-upcoming-appointment-notification')->dailyAt('9:00');

        $schedule->command('vault:notify-information-requested-rejected')->everyThreeHours();
        $schedule->command('vault:update-user-bitwarden-send')->everySixHours();

        $schedule->command('oneoffs:fetch-public-holidays')->yearly();

        $schedule->command('tasks:report-new-expenses-to-finance')->dailyAt('13:00');
        $schedule->command('tasks:report-new-expenses-to-host')->dailyAt('13:00');
        $schedule->command('tasks:send-new-expenses-app-notification-to-host')->dailyAt('13:00');
        $schedule->command('tasks:report-updated-expenses-to-host')->dailyAt('15:00');
        $schedule->command('tasks:user-expenses-list')->dailyAt('17:00');

        $schedule->command('task:send-billing-approval-host-notification')->cron('0 9 15-31 * *');
        $schedule->command('task:send-billing-approval-mail-notification')->cron('0 13 15-31 * *');

        $schedule->command('tasks:send-finance-invoice-lines-export')->monthlyOn(1, '9:00');

        $schedule->command('tasks:send-host-shared-documents-notification')->dailyAt('12:00');

        $schedule->command('logs:process-arrival-date-has-passed-logs')->dailyAt('09:00');

        $schedule->command('hris:sync-all-hris-employees')->dailyAt('01:00');
        $schedule->command('hris:sync-all-ats-applicants')->dailyAt('23:00');

        $schedule->command('feature:data-accuracy-report')->weeklyOn(1, '8:00');


        $schedule->command('task:fetch-nps-form-response')
            ->cron("0 */" . config('typeForm.npsFetchInterval') . " * * *");

        $schedule->command('logs:clean-old-entity-logs')->quarterly();

        $schedule->command('client:send-client-weekly-schedule')->weeklyOn(1, '7:00');

        /**
         * Running the service cost summary recalculation task daily at 1:00AM
         */
        $schedule->command('tasks:re-calculate-service-cost-summary')->dailyAt('1:00');

        $schedule->command('task:send-template-task-reminder-notification')
            ->everyThirtyMinutes()
            ->between('7:00', '22:00');

        $schedule->command('task:send-user-appointment-reminder')->dailyAt('9:00');
        $schedule->command('realmex:process')->everyMinute();

        $schedule->command('task:send-user-case-new-housing-list-notification')->everyMinute();
        $schedule->command('housing:purge-viewed-listings')->weekly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     * @SuppressWarnings ("php:S4833", "php:S2003")
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require_once base_path('routes/console.php');
    }
}
