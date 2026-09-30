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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstagramModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_has_instagram_comments_relationship()
    {
        $employee = Employee::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $employee->instagramComments());
    }

    public function test_instagram_account_has_media_and_sync_logs_relationships()
    {
        $account = new InstagramAccount();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $account->media());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $account->syncLogs());
    }

    public function test_instagram_media_has_account_metrics_and_comments_relationships()
    {
        $media = new InstagramMedia();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $media->instagramAccount());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $media->metricSnapshots());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $media->comments());
    }

    public function test_instagram_comment_has_media_and_employee_relationships()
    {
        $comment = new InstagramComment();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $comment->instagramMedia());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $comment->matchedEmployee());
    }

    public function test_instagram_media_metric_snapshot_has_media_relationship()
    {
        $snapshot = new InstagramMediaMetricSnapshot();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $snapshot->instagramMedia());
    }

    public function test_sync_log_has_account_relationship()
    {
        $log = new SyncLog();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $log->instagramAccount());
    }

    public function test_instagram_comment_uses_soft_deletes()
    {
        $this->assertContains('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(InstagramComment::class));
    }

    public function test_instagram_account_encrypts_access_token()
    {
        $account = InstagramAccount::create([
            'facebook_page_id' => '123',
            'instagram_user_id' => '456',
            'username' => 'testuser',
            'access_token' => 'secret_token_value',
        ]);

        $this->assertEquals('secret_token_value', $account->access_token);
        
        $rawDbValue = \DB::table('instagram_accounts')->where('id', $account->id)->value('access_token');
        $this->assertNotEquals('secret_token_value', $rawDbValue);
    }

    public function test_instagram_account_allows_null_facebook_page_id_with_uniqueness()
    {
        $account1 = InstagramAccount::create([
            'facebook_page_id' => null,
            'instagram_user_id' => 'ig_1',
            'username' => 'user_1',
        ]);
        $this->assertNull($account1->facebook_page_id);

        $account2 = InstagramAccount::create([
            'facebook_page_id' => null,
            'instagram_user_id' => 'ig_2',
            'username' => 'user_2',
        ]);
        $this->assertNull($account2->facebook_page_id);

        $this->assertNotEquals($account1->id, $account2->id);
    }

    public function test_raw_payload_and_timestamps_are_casted_correctly()
    {
        $media = new InstagramMedia([
            'raw_payload' => ['key' => 'value'],
            'published_at' => '2026-10-01 10:00:00',
        ]);
        
        $this->assertIsArray($media->raw_payload);
        $this->assertInstanceOf(Carbon::class, $media->published_at);

        $comment = new InstagramComment([
            'raw_payload' => ['id' => '123'],
            'commented_at' => '2026-10-01 10:00:00',
        ]);

        $this->assertIsArray($comment->raw_payload);
        $this->assertInstanceOf(Carbon::class, $comment->commented_at);

        $snapshot = new InstagramMediaMetricSnapshot([
            'raw_payload' => ['likes' => 10],
            'captured_at' => '2026-10-01 10:00:00',
        ]);

        $this->assertIsArray($snapshot->raw_payload);
        $this->assertInstanceOf(Carbon::class, $snapshot->captured_at);
        
        $syncLog = new SyncLog([
            'metadata' => ['status' => 'ok'],
            'started_at' => '2026-10-01 10:00:00',
            'finished_at' => '2026-10-01 10:05:00',
        ]);

        $this->assertIsArray($syncLog->metadata);
        $this->assertInstanceOf(Carbon::class, $syncLog->started_at);
        $this->assertInstanceOf(Carbon::class, $syncLog->finished_at);
        
        $period = new ReportingPeriod([
            'start_at' => '2026-10-01 10:00:00',
            'end_at' => '2026-11-01 10:00:00',
        ]);
        $this->assertInstanceOf(Carbon::class, $period->start_at);
        $this->assertInstanceOf(Carbon::class, $period->end_at);
        
        $employee = new Employee([
            'instagram_linked_at' => '2026-10-01 10:00:00',
        ]);
        $this->assertInstanceOf(Carbon::class, $employee->instagram_linked_at);
    }
}
