# Pest Testing Guidelines

Testing for the Auction Ops Simulator using Pest v4.

## Core Rules

All tests must be written using Pest. Use `php artisan make:test --pest {name}` to create tests.

You must not remove any tests or test files from the tests directory without approval. These are core to the application, not temporary helpers.

Tests should cover:
- All happy paths
- Failure paths
- Edge cases and weird paths

## Test Organization

- Feature tests live in `tests/Feature`
- Unit tests live in `tests/Unit`
- Browser tests live in `tests/Browser`

### Test Syntax

```php
it('is true', function () {
    expect(true)->toBeTrue();
});
```

## Running Tests

Run the minimal number of tests:
- All tests: `php artisan test --compact`
- All tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`
- Filter by name: `php artisan test --compact --filter=testName` (recommended after making changes)

When tests relating to your changes pass, ask the user if they'd like to run the entire test suite.

## Assertions

Use specific assertion methods instead of generic status codes:
- Use `assertForbidden()` instead of `assertStatus(403)`
- Use `assertNotFound()` instead of `assertStatus(404)`
- Use `assertSuccessful()` for success cases

Example:
```php
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);
    $response->assertSuccessful();
});
```

## Browser Testing (Pest 4)

Browser tests are incredibly powerful for this project.

### Features
- Use Laravel features like `Event::fake()`, `assertAuthenticated()`, model factories
- Use `RefreshDatabase` when needed for clean state
- Interact with the page: click, type, scroll, select, submit, drag-and-drop, gestures
- Take screenshots or pause for debugging
- Test on multiple browsers/devices/viewports/color schemes as requested

### Example

```php
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in');

    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')

    Notification::assertSent(ResetPassword::class);
});
```

Smoke testing example:
```php
$pages = visit(['/', '/about', '/contact']);
$pages->assertNoJavascriptErrors()->assertNoConsoleLogs();
```

## Mocking

Use `use function Pest\Laravel\mock;` to import the mock function, or use `$this->mock()` if existing tests do.

You can also create partial mocks using the same import or self method.

## Datasets

Use datasets in Pest to simplify tests with duplicated data. Particularly useful for testing validation rules:

```php
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
```

## Faker

Use methods like:
- `$this->faker->word()`
- `fake()->randomDigit()`

Follow existing conventions whether to use `$this->faker` or `fake()`.
