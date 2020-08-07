<?php

namespace XoopsModules\Mylinks\Providers;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * to use the provider:
 * $shot = new MylinksThumbshots();
 * $shot->setProviderPrivateKey(my_key);
 * $shot->setShotSize(array('width'=>120));
 * $shot->setSiteUrl("http://site_to_capture");
 * $mylinks_shotprovider = $shot->getProviderUrl();
 *
 * Then in the template use something like:
 *  <img src='<{$mylinks_shotprovider}>' target='_blank' alt='' style='margin: 3px 7px;'>
 *  and at the bottom of the page show the attribution
 *  echo $shot->getAttribution();
 */

use XoopsModules\Mylinks;

/**
 * MyLinks category.php
 *
 * Xoops mylinks - a multicategory links module
 *
 * @copyright ::  {@link http://www.zyspec.com ZySpec Incorporated}
 * @license   ::    {@link https://www.gnu.org/licenses/gpl-2.0.html GNU Public License}
 * @package   ::    mylinks
 * @subpackage:: class
 * @since     ::      3.11
 * @author    ::     zyspec <owner@zyspec.com>
 */

/**
 * Class MylinksPagepeeker
 */
class Pagepeeker implements Mylinks\ThumbPlugin
{
    private   $image_width   = 0;
    private   $image_height  = 0;
    private   $image_size    = 'm';
    private   $site_url      = null;
    private   $key           = null;
    private   $attribution   = '<a href="http://www.pagepeeker.com" target="_blank" title="Thumbnail Screenshots by PagePeeker">Thumbnail Screenshots by PagePeeker</a>';
    private   $provider_url  = 'http://free.pagepeeker.com/v2/thumbs.php';
    private   $provider_name = 'Pagepeeker';
    protected $_dirname      = null;

    /**
     * MylinksPagepeeker constructor.
     */
    public function __construct()
    {
        global $xoopsModule;
        $this->_dirname = \basename(\dirname(\dirname(__DIR__)));
    }

    /**
     * @return mixed|string
     */
    public function getProviderUrl()
    {
        $query_string = [
            'size' => $this->image_size,
            'url'  => $this->site_url,
        ];
        if (!empty($key)) {
            $query_string['code'] = $this->key;
            $query_string['wait'] = 5;  // generate screenshot if it doesn't exist (waits xx sec)
            \ksort($query_string);
        }
        $query = \http_build_query($query_string);
        $query = empty($query) ? '' : '?' . $query;

        // now fix provider URL
        $_mHandler = \xoops_getHandler('module');
        $_mlModule = $_mHandler->getByDirname($this->_dirname);
        $myKey     = $_mlModule->getInfo('shotpubkey');
        /* change the provider URL if the key is set */
        if (!empty($myKey)) {
            $providerUrl = \str_ireplace('http://free', 'http://api', $this->provider_url);
        } else {
            $providerUrl = $this->provider_url;
        }

        $providerUrl = $providerUrl . $query;

        return $providerUrl;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->provider_name;
    }

    /**
     * @param $sz
     * @return mixed|void
     */
    public function setShotSize($sz)
    {
        $validX  = [90, 120, 200, 400, 480];
        $validY  = [68, 90, 150, 300, 360];
        $sizeMap = [0 => 't', 1 => 's', 2 => 'm', 3 => 'l', 4 => 'x'];

        if (\is_array($sz)) { /* size is an array (width, height) */
            $x = (int)$sz['width'];
            if (\in_array($x, $validX)) {
                $Xdilav           = \array_flip($validX);
                $this->image_size = $sizeMap[$Xdilav[$x]];
            } else {
                $max_i = \count($validX);
                for ($i = 0; $i < $max_i; ++$i) {
                    if ($validX[$i] > $x) {
                        break;
                    }
                }
                $this->image_size = $sizeMap[$i];
            }
        } elseif (\is_numeric($sz)) { /* size is a number */
            $max_i = \count($validX);
            for ($i = 0; $i < $max_i; ++$i) {
                if ($validX[$i] > $sz) {
                    break;
                }
            }
            $this->image_size = $sizeMap[$i];
        } else { /* size is relative - t|s|m|l|x */
            $sz = mb_strtolower($sz);
            if (\array_key_exists($sz, $sizeMap)) {
                $this->image_size = $sizeMap[$sz];
            } else {
                $this->image_size = 'm';
            }
        }
        $paMezis            = \array_flip($sizeMap);
        $aKey               = $paMezis[$this->image_size];
        $this->image_width  = $validX[$aKey];
        $this->image_height = $validY[$aKey];
    }

    /**
     * @return array
     */
    public function getShotSize()
    {
        return ['width' => $this->image_width, 'height' => $this->image_height];
    }

    /**
     * @param $url
     * @return mixed|void
     */
    public function setSiteUrl($url)
    {
        //@todo: sanitize url;
        $this->site_url = \formatURL($url);
    }

    /**
     * @return string
     */
    public function getSiteUrl()
    {
        return \urlencode($this->site_url);
    }

    /**
     * @param null $attr
     */
    public function setAttribution($attr = null)
    {
        $this->attribution = $attr;
    }

    /**
     * @param int $allowhtml
     * @return string
     */
    public function getAttribution($allowhtml = 0)
    {
        if ($allowhtml) {
            return $this->attribution;
        }
        $myts = \MyTextSanitizer::getInstance();

        return $myts->htmlSpecialChars($this->attribution);
    }

    /**
     * @param $key
     * @return mixed|void
     */
    public function setProviderPublicKey($key)
    {
        $this->key = $key;
    }

    public function getProviderPublicKey()
    {
        return $this->key;
    }

    /**
     * @param $key
     * @return bool
     */
    public function setProviderPrivateKey($key)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function getProviderPrivateKey()
    {
        return false;
    }
}
