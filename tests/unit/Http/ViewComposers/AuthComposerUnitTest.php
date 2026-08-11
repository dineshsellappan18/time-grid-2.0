<?php

use App\Http\ViewComposers\AuthComposer;
use App\Models\User;

class AuthComposerUnitTest extends TestCase
{
    use CreateUser;

    /** @test */
    public function it_composes_the_view_for_guest_user()
    {
        $composer = new AuthComposer();

        $composer->compose();

        $view = view();

        $this->assertIsBool($view->shared('isGuest'));
        $this->assertIsBool($view->shared('signedIn'));
        $this->assertNull($view->shared('user'));
        $this->assertEquals('http://placehold.it/150x150', $view->shared('gravatarURL'));
        $this->assertInstanceOf(Illuminate\Support\Collection::class, $view->shared('appointments'));
    }

    /** @test */
    public function it_composes_the_view_for_authenticated_user()
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $composer = new AuthComposer();

        $composer->compose();

        $view = view();

        $this->assertIsBool($view->shared('isGuest'));
        $this->assertIsBool($view->shared('signedIn'));
        $this->assertEquals($user, $view->shared('user'));
        $this->assertIsString($view->shared('gravatarURL'));
        $this->assertInstanceOf(Illuminate\Support\Collection::class, $view->shared('appointments'));
    }
}
