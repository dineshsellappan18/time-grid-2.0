<?php

namespace Timegridio\Concierge\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;
use Timegridio\Concierge\Models\Business;

class BusinessPresenter extends BasePresenter
{
    public function __construct(Business $resource)
    {
        $this->wrappedObject = $resource;
    }

    /**
     * get Facebook Profile Public Picture.
     *
     * @param string $type Type of picture to print
     *
     * @return string HTML code to render img with facebook picture
     */
    public function facebookImg($type = 'square')
    {
        $name = e($this->wrappedObject->name);
        $fallbackUrl = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($name))) . "?s=100&d=identicon";

        if (!$this->wrappedObject->social_facebook) {
            return "<img class=\"img-thumbnail\" src=\"{$fallbackUrl}\" height=\"100\" width=\"100\" alt=\"{$name}\"/>";
        }

        $url = parse_url($this->wrappedObject->social_facebook);

        if (!array_key_exists('path', $url)) {
            return "<img class=\"img-thumbnail\" src=\"{$fallbackUrl}\" height=\"100\" width=\"100\" alt=\"{$name}\"/>";
        }

        $userId = trim($url['path'], '/');

        if ($url['path'] == '/profile.php') {
            parse_str($url['query'] ?? '', $parts);
            $userId = $parts['id'] ?? '';
        }

        if (empty($userId)) {
            return "<img class=\"img-thumbnail\" src=\"{$fallbackUrl}\" height=\"100\" width=\"100\" alt=\"{$name}\"/>";
        }

        $graphUrl = "https://graph.facebook.com/{$userId}/picture?type={$type}";

        return "<img class=\"img-thumbnail media-object\" src=\"{$graphUrl}\" height=\"100\" width=\"100\" alt=\"{$name}\" onerror=\"this.onerror=null;this.src='{$fallbackUrl}';\"/>";
    }

    /**
     * get Google Static Map img.
     *
     * @param int $zoom Zoom Level
     *
     * @return string HTML code to render img with map
     */
    public function staticMap($zoom = 15)
    {
        $data = [
            'center'         => $this->wrappedObject->postal_address,
            'zoom'           => intval($zoom),
            'scale'          => '2',
            'size'           => '180x100',
            'maptype'        => 'roadmap',
            'format'         => 'gif',
            'visual_refresh' => 'true', ];

        $src = 'http://maps.googleapis.com/maps/api/staticmap?'.http_build_query($data, '', '&amp;');

        return "<img class=\"img-fluid img-thumbnail d-block mx-auto\" width=\"180\" height=\"100\" src=\"$src\"/>";
    }

    /**
     * get Industry Icon.
     *
     * @return string HTML code to render img with icon
     */
    public function industryIcon()
    {
        if ($this->wrappedObject->pref('cover_photo_url')) {
            $src = $this->wrappedObject->pref('cover_photo_url');
        } else {
            $slug = $this->wrappedObject->category->slug ?? 'default';
            $path = public_path("img/industries/{$slug}.png");
            $src = file_exists($path)
                ? asset("/img/industries/{$slug}.png")
                : asset('/img/industries/default.png');
        }

        return "<img class=\"img-fluid d-block mx-auto\" src=\"{$src}\" alt=\"" . e($this->wrappedObject->category->name ?? '') . "\"/>";
    }
}
