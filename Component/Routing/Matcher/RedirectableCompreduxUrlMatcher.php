<?php


namespace NineThousand\Bundle\NineThousandCompreduxBundle\Component\Routing\Matcher;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcherInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;
use NineThousand\Bundle\NineThousandCompreduxBundle\Component\Routing\Matcher\CompreduxUrlMatcher;

/**
 * RedirectableCompreduxUrlMatcher extends CompreduxUrlMatcherUrlMatcher.
 *
 * @author Jesse Greathouse <jesse.greathouse@gmail.com>
 *
 */
class RedirectableCompreduxUrlMatcher extends CompreduxUrlMatcher implements RedirectableUrlMatcherInterface
{
    /**
     * Redirects the user to another URL.
     *
     * @param string  $path   The path info to redirect to.
     * @param string  $route  The route that matched
     * @param string  $scheme The URL scheme (null to keep the current one)
     *
     * @return array An array of parameters
     */
    public function redirect($path, $route, $scheme = null)
    {
        return array(
            '_controller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::urlRedirectAction',
            'path'        => $path,
            'permanent'   => true,
            'scheme'      => $scheme,
            'httpPort'    => $this->context->getHttpPort(),
            'httpsPort'   => $this->context->getHttpsPort(),
            '_route'      => $route,
        );
    }
}

