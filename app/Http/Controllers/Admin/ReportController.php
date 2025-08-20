<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Progress;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Get all estimators for the dropdown
        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();
        
        $stats = null;
        $selectedUser = null;
        $filters = [
            'user_id' => $request->get('user_id'),
            'month' => $request->get('month'),
            'year' => $request->get('year', now()->year),
        ];
        
        // Only generate stats if a user is selected
        if ($request->filled('user_id')) {
            $selectedUser = User::find($request->get('user_id'));
            if ($selectedUser) {
                $stats = $this->generateUserStats($selectedUser, $filters);
            }
        }
        
        return view('admin.reports.index', compact('estimators', 'stats', 'selectedUser', 'filters'));
    }
    
    private function generateUserStats(User $user, array $filters)
    {
        $query = Project::where('assigned_to', $user->id);
        
        // Apply date filters
        if (!empty($filters['year'])) {
            $query->whereYear('created_at', $filters['year']);
        }
        
        if (!empty($filters['month'])) {
            $query->whereMonth('created_at', $filters['month']);
        }
        
        $projects = $query->get();
        
        // Basic Project Stats
        $totalProjects = $projects->count();
        
        // Status Distribution
        $statusCounts = $projects->groupBy('status')->map->count()->toArray();
        
        // Type Distribution
        $typeCounts = $projects->groupBy('type')->map->count()->toArray();
        
        // Progress Stats
        $progressQuery = Progress::whereHas('project', function($q) use ($user) {
            $q->where('assigned_to', $user->id);
        });
        
        if (!empty($filters['year'])) {
            $progressQuery->whereYear('created_at', $filters['year']);
        }
        
        if (!empty($filters['month'])) {
            $progressQuery->whereMonth('created_at', $filters['month']);
        }
        
        $progressEntries = $progressQuery->get();
        $totalHours = $progressEntries->sum('total_hours');
        $totalSqft = $progressEntries->sum('total_sqft');
        $totalSlabs = $progressEntries->sum('total_slabs');
        
        // Proposal Stats
        $proposalQuery = Proposal::whereHas('project', function($q) use ($user) {
            $q->where('assigned_to', $user->id);
        });
        
        if (!empty($filters['year'])) {
            $proposalQuery->whereYear('created_at', $filters['year']);
        }
        
        if (!empty($filters['month'])) {
            $proposalQuery->whereMonth('created_at', $filters['month']);
        }
        
        $proposals = $proposalQuery->get();
        $totalProposals = $proposals->count();
        
        // Response Stats
        $respondedYes = $proposals->where('responded', 'yes')->count();
        $respondedNo = $proposals->where('responded', 'no')->count();
        $noResponse = $proposals->whereNull('responded')->count();
        
        // Follow-up Stats
        $firstFollowUps = $proposals->whereNotNull('first_follow_up_date')->count();
        $secondFollowUps = $proposals->whereNotNull('second_follow_up_date')->count();
        $thirdFollowUps = $proposals->whereNotNull('third_follow_up_date')->count();
        
        $firstFollowUpResponded = $proposals->where('first_follow_up_respond', 'yes')->count();
        $secondFollowUpResponded = $proposals->where('second_follow_up_respond', 'yes')->count();
        $thirdFollowUpResponded = $proposals->where('third_follow_up_respond', 'yes')->count();
        
        // Win/Loss Stats
        $gcWins = $proposals->where('result_gc', 'win')->count();
        $gcLosses = $proposals->where('result_gc', 'loss')->count();
        $artWins = $proposals->where('result_art', 'win')->count();
        $artLosses = $proposals->where('result_art', 'loss')->count();
        
        // Financial Stats
        $totalProposalValue = $proposals->sum('price_original');
        $totalVeValue = $proposals->sum('price_ve');
        $totalGcPrice = $proposals->sum('gc_price');
        $veSavings = $totalProposalValue - $totalVeValue;
        
        return [
            // Project Stats
            'total_projects' => $totalProjects,
            
            // Status & Type Distribution
            'status_counts' => $statusCounts,
            'type_counts' => $typeCounts,
            
            // Progress Stats
            'total_hours' => round($totalHours, 2),
            'total_sqft' => round($totalSqft, 2),
            'total_slabs' => $totalSlabs,
            'progress_entries' => $progressEntries->count(),
            
            // Proposal Stats
            'total_proposals' => $totalProposals,
            'responded_yes' => $respondedYes,
            'responded_no' => $respondedNo,
            'no_response' => $noResponse,
            'response_rate' => $totalProposals > 0 ? round(($respondedYes / $totalProposals) * 100, 1) : 0,
            
            // Follow-up Stats
            'first_follow_ups' => $firstFollowUps,
            'second_follow_ups' => $secondFollowUps,
            'third_follow_ups' => $thirdFollowUps,
            'first_follow_up_responded' => $firstFollowUpResponded,
            'second_follow_up_responded' => $secondFollowUpResponded,
            'third_follow_up_responded' => $thirdFollowUpResponded,
            'first_follow_up_response_rate' => $firstFollowUps > 0 ? round(($firstFollowUpResponded / $firstFollowUps) * 100, 1) : 0,
            'second_follow_up_response_rate' => $secondFollowUps > 0 ? round(($secondFollowUpResponded / $secondFollowUps) * 100, 1) : 0,
            'third_follow_up_response_rate' => $thirdFollowUps > 0 ? round(($thirdFollowUpResponded / $thirdFollowUps) * 100, 1) : 0,
            
            'gc_wins' => $gcWins,
            'gc_losses' => $gcLosses,
            'art_wins' => $artWins,
            'art_losses' => $artLosses,
            'gc_win_rate' => ($gcWins + $gcLosses) > 0 ? round(($gcWins / ($gcWins + $gcLosses)) * 100, 1) : 0,
            'art_win_rate' => ($artWins + $artLosses) > 0 ? round(($artWins / ($artWins + $artLosses)) * 100, 1) : 0,
            
            // Financial Stats
            'total_proposal_value' => $totalProposalValue,
            'total_ve_value' => $totalVeValue,
            'total_gc_price' => $totalGcPrice,
            've_savings' => $veSavings,
            've_savings_rate' => $totalProposalValue > 0 ? round(($veSavings / $totalProposalValue) * 100, 1) : 0,
        ];
    }
}