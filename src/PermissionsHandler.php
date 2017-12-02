<?php

namespace PermissionsHandler;

/*
 * @Auther: Mohamed Nagy
 */
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\FileCacheReader;

class PermissionsHandler
{
    protected $annotationReader;
    protected $config = [];

    public function __construct()
    {
        $this->annotationReader = new FileCacheReader(
            new AnnotationReader(),
            storage_path('PermissionsHandler/Cache'),
            (strpos(strtolower(env('APP_ENV')), 'prod') === false)
        );

        $this->config = config('permissionsHandler');
    }

    /**
     * check if a user has permission to access specific route.
     *
     * @return bool
     */
    public function canGo($request)
    {
        $annotations = $this->getAnnotationsFromRequest($request);
        if ($this->config['aggressiveMode'] == true && empty($annotations)) {
            return false;
        } elseif ($this->config['aggressiveMode'] == false && empty($annotations)) {
            return true;
        }
        foreach ($annotations as $annotation) {
            if (! $annotation->check()) {
                return  false;
            }
        }

        return true;
    }

    /**
     * check if the current route is excluded from permissions rules.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return bool
     */
    public function isExcludedRoute($request)
    {
        foreach ($this->config['excludedRoutes'] as $route) {
            if ($request->is($route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * get the assigned annotations from the a route.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return array
     */
    public function getAnnotationsFromRequest($request)
    {
        $annotations = [];
        $actionName = $request->route()->getActionName();
        if (strpos($actionName, '@') !== false) {
            $action = explode('@', $actionName);
            $class = $action[0];
            $method = $action[1];
            $reflectionMethod = new \ReflectionMethod($class, $method);
            $annotations = $this->annotationReader->getMethodAnnotations($reflectionMethod);
        }

        return $annotations;
    }

    /**
     * Clear all cached annotations.
     *
     * @return void
     */
    public function clearCachedAnnotations()
    {
        $this->annotationReader->clearLoadedAnnotations();
    }

    /**
     * Retrive permissions handler user.
     *
     * @param int $id
     * @return void
     */
    public function user($id = null)
    {
        $user = $this->config['user'];
        if ($id) {
            return $user::find($id);
        }

        $user = auth()->user();
        if ($user) {
            return $user;
        }

        return new $user();
    }
}
