<?php

namespace Mellivora\Pagination;

use ArrayIterator;
use Closure;
use Mellivora\Support\Collection;
use Mellivora\Support\Contracts\Htmlable;
use Slim\Http\Uri;

abstract class AbstractPaginator implements Htmlable
{
    /**
     * All of the items being paginated.
     *
     * @var \Mellivora\Support\Collection
     */
    protected $items;

    /**
     * The number of items to be shown per page.
     *
     * @var int
     */
    protected $perPage;

    /**
     * The current page being "viewed".
     *
     * @var int
     */
    protected $currentPage;

    /**
     * The base path to assign to all URLs.
     *
     * @var string
     */
    protected $path = '/';

    /**
     * The query parameters to add to all URLs.
     *
     * @var array
     */
    protected $query = [];

    /**
     * The URL fragment to add to all URLs.
     *
     * @var null|string
     */
    protected $fragment;

    /**
     * The query string variable used to store the page.
     *
     * @var string
     */
    protected $pageName = 'page';

    /**
     * The current page resolver callback.
     *
     * @var \Closure
     */
    protected static $currentPathResolver;

    /**
     * The current page resolver callback.
     *
     * @var \Closure
     */
    protected static $currentPageResolver;

    /**
     * The view factory resolver callback.
     *
     * @var \Closure
     */
    protected static $viewFactoryResolver;

    /**
     * The default pagination view.
     *
     * @var string
     */
    public static $defaultView = 'pagination::default';

    /**
     * The default "simple" pagination view.
     *
     * @var string
     */
    public static $defaultSimpleView = 'pagination::simple-default';

    /**
     * Determine if the given value is a valid page number.
     *
     * @param int $page
     *
     * @return bool
     */
    protected function isValidPageNumber($page)
    {
        return $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Get the URL for the previous page.
     *
     * @return null|string
     */
    public function previousPageUrl()
    {
        if ($this->currentPage() > 1) {
            return $this->url($this->currentPage() - 1);
        }
    }

    /**
     * Create a range of pagination URLs.
     *
     * @param int $start
     * @param int $end
     *
     * @return string
     */
    public function getUrlRange($start, $end)
    {
        return collect(range($start, $end))->mapWithKeys(function ($page) {
            return [$page => $this->url($page)];
        })->all();
    }

    /**
     * Get the URL for a given page number.
     *
     * @param int $page
     *
     * @return string
     */
    public function url($page)
    {
        if ($page <= 0) {
            $page = 1;
        }

        // If we have any extra query string key / value pairs that need to be added
        // onto the URL, we will put them in query string form and then attach it
        // to the URL. This allows for extra information like sortings storage.
        $parameters = [$this->pageName => $page];

        if (count($this->query) > 0) {
            $parameters = array_merge($this->query, $parameters);
        }

        $uri = Uri::createFromString($this->path);

        parse_str($uri->getQuery(), $queryParts);

        return $uri->withQuery(http_build_query($parameters + $queryParts))
            ->withFragment($this->buildFragment())
            ->__toString();
    }

    /**
     * Get / set the URL fragment to be appended to URLs.
     *
     * @param null|string $fragment
     *
     * @return null|$this|string
     */
    public function fragment($fragment = null)
    {
        if (is_null($fragment)) {
            return $this->fragment;
        }

        $this->fragment = $fragment;

        return $this;
    }

    /**
     * Add a set of query string values to the paginator.
     *
     * @param array|string $key
     * @param null|string  $value
     *
     * @return $this
     */
    public function appends($key, $value = null)
    {
        if (is_array($key)) {
            return $this->appendArray($key);
        }

        return $this->addQuery($key, $value);
    }

    /**
     * Add an array of query string values.
     *
     * @param array $keys
     *
     * @return $this
     */
    protected function appendArray(array $keys)
    {
        foreach ($keys as $key => $value) {
            $this->addQuery($key, $value);
        }

        return $this;
    }

    /**
     * Add a query string value to the paginator.
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    protected function addQuery($key, $value)
    {
        if ($key !== $this->pageName) {
            $this->query[$key] = $value;
        }

        return $this;
    }

    /**
     * Build the full fragment portion of a URL.
     *
     * @return string
     */
    protected function buildFragment()
    {
        return $this->fragment ? '#' . $this->fragment : '';
    }

    /**
     * Get the slice of items being paginated.
     *
     * @return array
     */
    public function items()
    {
        return $this->items->all();
    }

    /**
     * Get the number of the first item in the slice.
     *
     * @return int
     */
    public function firstItem()
    {
        return count($this->items) > 0 ? ($this->currentPage - 1) * $this->perPage + 1 : null;
    }

    /**
     * Get the number of the last item in the slice.
     *
     * @return int
     */
    public function lastItem()
    {
        return count($this->items) > 0 ? $this->firstItem() + $this->count() - 1 : null;
    }

    /**
     * Get the number of items shown per page.
     *
     * @return int
     */
    public function perPage()
    {
        return $this->perPage;
    }

    /**
     * Determine if there are enough items to split into multiple pages.
     *
     * @return bool
     */
    public function hasPages()
    {
        return $this->currentPage() !== 1 || $this->hasMorePages();
    }

    /**
     * Determine if the paginator is on the first page.
     *
     * @return bool
     */
    public function onFirstPage()
    {
        return $this->currentPage() <= 1;
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function currentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get the query string variable used to store the page.
     *
     * @return string
     */
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * Set the query string variable used to store the page.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setPageName($name)
    {
        $this->pageName = $name;

        return $this;
    }

    /**
     * Set the base path to assign to all URLs.
     *
     * @param string $path
     *
     * @return $this
     */
    public function withPath($path)
    {
        return $this->setPath($path);
    }

    /**
     * Set the base path to assign to all URLs.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Resolve the current request path or return the default value.
     *
     * @param string $default
     *
     * @return string
     */
    public static function resolveCurrentPath($default = '/')
    {
        if (isset(static::$currentPathResolver)) {
            return call_user_func(static::$currentPathResolver);
        }

        return $default;
    }

    /**
     * Set the current request path resolver callback.
     *
     * @param \Closure $resolver
     *
     * @return void
     */
    public static function currentPathResolver(Closure $resolver)
    {
        static::$currentPathResolver = $resolver;
    }

    /**
     * Resolve the current page or return the default value.
     *
     * @param string $pageName
     * @param int    $default
     *
     * @return int
     */
    public static function resolveCurrentPage($pageName = 'page', $default = 1)
    {
        if (isset(static::$currentPageResolver)) {
            return call_user_func(static::$currentPageResolver, $pageName);
        }

        return $default;
    }

    /**
     * Set the current page resolver callback.
     *
     * @param \Closure $resolver
     *
     * @return void
     */
    public static function currentPageResolver(Closure $resolver)
    {
        static::$currentPageResolver = $resolver;
    }

    /**
     * Get an instance of the view factory from the resolver.
     *
     * @return \Mellivora\Support\Contracts\View\Factory
     */
    public static function viewFactory()
    {
        return call_user_func(static::$viewFactoryResolver);
    }

    /**
     * Set the view factory resolver callback.
     *
     * @param \Closure $resolver
     *
     * @return void
     */
    public static function viewFactoryResolver(Closure $resolver)
    {
        static::$viewFactoryResolver = $resolver;
    }

    /**
     * Set the default pagination view.
     *
     * @param string $view
     *
     * @return void
     */
    public static function defaultView($view)
    {
        static::$defaultView = $view;
    }

    /**
     * Set the default "simple" pagination view.
     *
     * @param string $view
     *
     * @return void
     */
    public static function defaultSimpleView($view)
    {
        static::$defaultSimpleView = $view;
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items->all());
    }

    /**
     * Determine if the list of items is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    /**
     * Get the number of items for the current page.
     *
     * @return int
     */
    public function count()
    {
        return $this->items->count();
    }

    /**
     * Get the paginator's underlying collection.
     *
     * @return \Mellivora\Support\Collection
     */
    public function getCollection()
    {
        return $this->items;
    }

    /**
     * Set the paginator's underlying collection.
     *
     * @param \Mellivora\Support\Collection $collection
     *
     * @return $this
     */
    public function setCollection(Collection $collection)
    {
        $this->items = $collection;

        return $this;
    }

    /**
     * Determine if the given item exists.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->items->has($key);
    }

    /**
     * Get the item at the given offset.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items->get($key);
    }

    /**
     * Set the item at the given offset.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->items->put($key, $value);
    }

    /**
     * Unset the item at the given key.
     *
     * @param mixed $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->items->forget($key);
    }

    /**
     * Render the contents of the paginator to HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        return (string) $this->render();
    }

    /**
     * Make dynamic calls into the collection.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->getCollection()->{$method}(...$parameters);
    }

    /**
     * Render the contents of the paginator when casting to string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->render();
    }
}
