# quarks/laravel-locking

Easily implement optimistic [Eloquent](https://laravel.com/docs/6.x/eloquent) model locking feature to your [Laravel](https://laravel.com/) app.

[![Latest Version][latest-version-image]][latest-version-url]
[![Downloads][downloads-image]][downloads-url]
[![PHP Version][php-version-image]][php-version-url]
[![License][license-image]](LICENSE)

### Installation

```bash
composer require quarks/laravel-locking
```

### Usage

In your migration classes, add the version column to your table as below:

```php
/**
 * Run the migrations.
 *
 * @return void
 */
public function up()
{
    Schema::table('blog_posts', function (Blueprint $table) {
        $table->lockVersion();
    });
}

/**
 * Reverse the migrations.
 *
 * @return void
 */
public function down()
{
    Schema::table('blog_posts', function (Blueprint $table) {
        $table->dropLockVersion();
    });
}
```

Then add the `LocksVersion` trait your model classes as follows:

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Quarks\Laravel\Auditors\HasAuditors;

class BlogPost extends Authenticatable
{
    use LocksVersion;
}
```

In your blade templates, include the current lock version as part of the form using the `lockVersion` directive as below:

```html
<form method="post">
    @lockInput($blogPost)
    
    <!-- more fields -->
</form>
```

In your controllers, fill the lock version from request using below helper:

```php
namespace App\Http\Controllers;

use Quarks\Laravel\Locking\LockedVersionMismatchException;

// ... other imports

class BlogPostController extends Controller
{
    public function update(BlogPost $blogPost, BlogPostRequest $request)
    {
        $data = $request->validated();
        $blogPost->fill($data);
        $blogPost->fillLockVersion();
        try {
            $blogPost->save();
        } catch (LockedVersionMismatchException $e) {
            abort(409);
        }
    }
}
```

Your model update can now be simply protected from concurrent updates as shown above.

### License

See [LICENSE](LICENSE) file.

[latest-version-image]: https://img.shields.io/github/release/qtsolv/laravel-locking.svg?style=flat-square
[latest-version-url]: https://github.com/qtsolv/laravel-locking/releases
[downloads-image]: https://img.shields.io/packagist/dt/quarks/laravel-locking.svg?style=flat-square
[downloads-url]: https://packagist.org/packages/quarks/laravel-locking
[php-version-image]: http://img.shields.io/badge/php-7.2+-8892be.svg?style=flat-square
[php-version-url]: https://www.php.net/downloads
[license-image]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
