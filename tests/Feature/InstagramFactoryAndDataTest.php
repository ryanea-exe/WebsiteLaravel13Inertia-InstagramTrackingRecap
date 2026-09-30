<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\InstagramAccount;
use App\Models\InstagramComment;
use App\Models\InstagramMedia;
use App\Models\InstagramMediaMetricSnapshot;
use App\Models\ReportingPeriod;
use App\Models\SyncLog;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstagramFactoryAndDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_default_unlinked_and_linked_states()
    {
        $unlinkedEmployee = Employee::factory()->create();
        $this->assertNull($unlinkedEmployee->instagram_user_id);
        $this->assertEquals('UNLINKED', $unlinkedEmployee->instagram_link_status);
        $this->assertNull($unlinkedEmployee->instagram_linked_at);

        $linkedEmployee = Employee::factory()->linkedToInstagram()->create();
        $this->assertNotNull($linkedEmployee->instagram_user_id);
        $this->assertEquals('LINKED', $linkedEmployee->instagram_link_status);
        $this->assertNotNull($linkedEmployee->instagram_linked_at);
    }

    public function test_instagram_account_factory_and_encryption()
    {
        $account = InstagramAccount::factory()->create();
        $this->assertStringStartsWith('dummy-access-token-', $account->access_token);

        $rawDbValue = \DB::table('instagram_accounts')->where('id', $account->id)->value('access_token');
        $this->assertNotEquals($account->access_token, $rawDbValue);
    }

    public function test_media_and_multiple_metric_snapshots()
    {
        $media = InstagramMedia::factory()
            ->has(InstagramMediaMetricSnapshot::factory()->count(3), 'metricSnapshots')
            ->create();

        $this->assertCount(3, $media->metricSnapshots);
        $this->assertNotNull($media->instagramAccount);
    }

    public function test_instagram_comment_states_and_identities()
    {
        $unmatchedComment = InstagramComment::factory()->unmatched()->create();
        $this->assertNull($unmatchedComment->matched_employee_id);
        
        $employee = Employee::factory()->linkedToInstagram()->create();
        
        $matchedComment = InstagramComment::factory()->fromEmployee($employee)->create();
        $this->assertEquals($employee->id, $matchedComment->matched_employee_id);
        $this->assertEquals($employee->instagram_user_id, $matchedComment->commenter_instagram_user_id);

        $deletedComment = InstagramComment::factory()->deleted()->create();
        $this->assertNotNull($deletedComment->deleted_at);
        $this->assertTrue($deletedComment->trashed());
        
        // Assert comment doesn't show in normal queries
        $this->assertNull(InstagramComment::find($deletedComment->id));
        $this->assertNotNull(InstagramComment::withTrashed()->find($deletedComment->id));
    }
    
    public function test_relationship_integration_chain()
    {
        $account = InstagramAccount::factory()->create();
        $media = InstagramMedia::factory()->create(['instagram_account_id' => $account->id]);
        $employee = Employee::factory()->linkedToInstagram()->create();
        $comment = InstagramComment::factory()->create([
            'instagram_media_id' => $media->id,
            'matched_employee_id' => $employee->id,
            'commenter_instagram_user_id' => $employee->instagram_user_id
        ]);
        
        $this->assertEquals($employee->id, $comment->matchedEmployee->id);
        $this->assertEquals($media->id, $comment->instagramMedia->id);
        $this->assertEquals($account->id, $media->instagramAccount->id);
    }

    public function test_commenter_id_can_be_different_from_matched_employee_and_nullable()
    {
        $comment = InstagramComment::factory()->create([
            'commenter_instagram_user_id' => 'dummy_ig_external_999',
            'matched_employee_id' => null
        ]);
        $this->assertEquals('dummy_ig_external_999', $comment->commenter_instagram_user_id);
        
        $commentNull = InstagramComment::factory()->create([
            'commenter_instagram_user_id' => null
        ]);
        $this->assertNull($commentNull->commenter_instagram_user_id);
    }

    public function test_duplicate_instagram_user_id_protection()
    {
        Employee::factory()->create(['instagram_user_id' => 'duplicate_123']);
        
        $this->expectException(QueryException::class);
        Employee::factory()->create(['instagram_user_id' => 'duplicate_123']);
    }

    public function test_reporting_period_interval_logic()
    {
        $start = Carbon::parse('2026-09-01 00:00:00');
        $end = Carbon::parse('2026-10-01 00:00:00');
        
        $period = ReportingPeriod::factory()->create([
            'start_at' => $start,
            'end_at' => $end,
        ]);
        
        $commentInside1 = InstagramComment::factory()->create(['commented_at' => '2026-09-01 00:00:00']);
        $commentInside2 = InstagramComment::factory()->create(['commented_at' => '2026-09-30 23:59:59']);
        $commentOutside1 = InstagramComment::factory()->create(['commented_at' => '2026-10-01 00:00:00']);
        $commentOutside2 = InstagramComment::factory()->create(['commented_at' => '2026-08-31 23:59:59']);
        
        $countInside = InstagramComment::where('commented_at', '>=', $period->start_at)
            ->where('commented_at', '<', $period->end_at)
            ->count();
            
        // Expected count is exactly the 2 comments inside the [start, end) interval
        $this->assertEquals(2, $countInside);
    }

    public function test_sync_log_factory()
    {
        $log = SyncLog::factory()->create();
        $this->assertNotNull($log->instagramAccount);
        $this->assertEquals('FULL', $log->sync_type);
    }
}
