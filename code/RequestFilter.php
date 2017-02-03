<?php

namespace SilverStripe\Intercom;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\RequestFilter as BaseRequestFilter;
use SilverStripe\Control\Session;
use SilverStripe\ORM\DataModel;
use SilverStripe\View\ViewableData;

/**
 * Add HTML content before the </body> of a full HTML page.
 * Used to include IntercomScriptTags into a page
 */
class RequestFilter implements BaseRequestFilter
{
    /**
     * Does nothing
     */
    public function preRequest(HTTPRequest $request, Session $session, DataModel $model)
    {
    }

    /**
     * Provide a ViewableData object that will render the tags to include.
     */
    public function setTagProvider(ViewableData $tagProvider)
    {
        $this->tagProvider = $tagProvider;
    }

    /**
     * Adds Intercom script tags just before the body
     */
    public function postRequest(HTTPRequest $request, HTTPResponse $response, DataModel $model)
    {
        $mime = $response->getHeader('Content-Type');
        if (!$mime || strpos($mime, 'text/html') !== false) {
            $tags = $this->tagProvider->forTemplate();

            if ($tags) {
                $content = $response->getBody();
                $content = preg_replace("/(<\/body[^>]*>)/i", $tags . "\\1", $content);
                $response->setBody($content);
            }
        }
    }
}
