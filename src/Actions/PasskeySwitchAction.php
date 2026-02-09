<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Concerns\HasInheritanceCheck;
use Jampire\MoonshinePasskeys\Models\Contracts\PasskeyContract;
use Jampire\MoonshinePasskeys\Services\Result;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final readonly class PasskeySwitchAction implements Actionable
{
    use HasInheritanceCheck;

    public function execute(Authenticatable|PasskeyContract $user, bool $activate): Result
    {
        try {
            $this->checkForRelatedModel($user);

            $user->passkeyMeta()->updateOrCreate(
                ['personable_type' => $user::class, 'personable_id' => $user->id],
                ['is_active' => $activate],
            );

            return Result::ok('success');
        } catch (\Throwable $e) {
            captureException($e, self::class, [
                'user_id' => $user->id,
                'activate' => $activate,
            ]);

            return Result::err(error: 'common.operation-failed', debug: $e->getMessage());
        }
    }
}
