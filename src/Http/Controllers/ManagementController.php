<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Jampire\MoonshinePasskeys\Actions\Contracts\Actionable;
use Jampire\MoonshinePasskeys\Actions\PasskeySwitchAction;
use Jampire\MoonshinePasskeys\Concerns\HasInheritanceCheck;
use MoonShine\Crud\Contracts\Notifications\MoonShineNotificationContract;
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use MoonShine\Laravel\MoonShineAuth;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
final class ManagementController extends MoonShineController
{
    use HasInheritanceCheck;

    /**
     * @param PasskeySwitchAction $action
     */
    public function __construct(MoonShineNotificationContract $notification, private readonly Actionable $action)
    {
        parent::__construct($notification);
    }

    public function activate(): JsonResponse
    {
        $result = $this->action->execute(MoonShineAuth::getGuard()->user(), true);

        return Response::result($result, errorStatus: Response::HTTP_NOT_FOUND);
    }

    public function deactivate(): JsonResponse
    {
        $result = $this->action->execute(MoonShineAuth::getGuard()->user(), false);

        return Response::result($result, errorStatus: Response::HTTP_NOT_FOUND);
    }
}
