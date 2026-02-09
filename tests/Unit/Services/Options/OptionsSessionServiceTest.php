<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Session;
use Jampire\MoonshinePasskeys\Services\Options\OptionsSessionService;

describe('OptionsSessionService', function (): void {
    beforeEach(function (): void {
        config(['passkeys.options_store.ttl' => 60]);
        Session::flush();
    });

    it('saves and retrieves registration options', function (): void {
        $service = new OptionsSessionService();
        $options = 'test-registration-options';

        $service->saveRegistrationOptions($options);
        $retrieved = $service->getRegistrationOptions();

        expect($retrieved)->toBe($options);
    });

    it('returns null for registration options when not stored', function (): void {
        $service = new OptionsSessionService();

        expect($service->getRegistrationOptions())->toBeNull();
    });

    it('consumes registration options on retrieval (returns null on second call)', function (): void {
        $service = new OptionsSessionService();
        $service->saveRegistrationOptions('my-options');

        $first = $service->getRegistrationOptions();
        $second = $service->getRegistrationOptions();

        expect($first)->toBe('my-options')
            ->and($second)->toBeNull();
    });

    it('returns null for expired registration options', function (): void {
        $service = new OptionsSessionService();
        $service->saveRegistrationOptions('test');

        // Travel past the TTL
        $this->travel(120)->seconds();

        expect($service->getRegistrationOptions())->toBeNull();
    });

    it('saves and retrieves authentication options', function (): void {
        $service = new OptionsSessionService();
        $options = 'test-authentication-options';

        $service->saveAuthenticationOptions($options);
        $retrieved = $service->getAuthenticationOptions();

        expect($retrieved)->toBe($options);
    });

    it('returns null for authentication options when not stored', function (): void {
        $service = new OptionsSessionService();

        expect($service->getAuthenticationOptions())->toBeNull();
    });

    it('consumes authentication options on retrieval', function (): void {
        $service = new OptionsSessionService();
        $service->saveAuthenticationOptions('auth-options');

        $first = $service->getAuthenticationOptions();
        $second = $service->getAuthenticationOptions();

        expect($first)->toBe('auth-options')
            ->and($second)->toBeNull();
    });

    it('returns null for expired authentication options', function (): void {
        $service = new OptionsSessionService();
        $service->saveAuthenticationOptions('auth');

        $this->travel(120)->seconds();

        expect($service->getAuthenticationOptions())->toBeNull();
    });
});
