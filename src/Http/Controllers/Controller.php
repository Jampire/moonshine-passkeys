<?php

declare(strict_types=1);

namespace Jampire\MoonshinePasskeys\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @author Dzianis Kotau <me@dzianiskotau.com>
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests;
}
