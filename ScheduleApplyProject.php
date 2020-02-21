<?php

namespace App\Console\Commands;

use Mail;
use App\Mail\ScheduleApplyProjectMail;
use App\Project;
use App\ProjectProposal;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScheduleApplyProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:apply';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule project applied email to send every night.';

    /**
     * Create a new command instance.
     *
     * @author TintNaingWin
     */

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @author TintNaingWin
     */

    public function handle()
    {
        $day = 'today';
        $date = new Carbon($day);

//        $projects = Project::whereIn('id', [254,255, 256,257])->get();
//        foreach ($projects as $project){
//            $this->info('Name : ' . $project->employer->user->name);
//            $this->notifyProjectApply($project->employer->user, $project);
//        }

        $proposal = ProjectProposal::where(function ($query){
            $query->where('status', "pending")->orWhere('status', "edited");
        })->whereDate('created_at', $date->format('Y-m-d'))->get();

        if (!$proposal->isEmpty()){
            $p = $proposal->groupBy('project_id');

            foreach ($p as $project_id => $proposals){
                $project = Project::where('id',$project_id)->first();

                $this->info('Name : '.$project->employer->user->name);

                $this->notifyProjectApply($project->employer->user,$project);

            }
        }

    }

    /**
     * @param User $user
     * @param $project
     * @author TintNaingWin
     */
    protected function notifyProjectApply(User $user, $project)
    {
        Mail::to($user)->queue(new ScheduleApplyProjectMail($user,$project));
    }

    /**
     * @param User $user
     * @param $project
     * @author TintNaingWin
     */
    protected function notifyLaterProjectApply(User $user, $project)
    {
        $when = Carbon::now()->addMinutes(60);

        Mail::to($user)->later($when, new ScheduleApplyProjectMail($user,$project));
    }
}
