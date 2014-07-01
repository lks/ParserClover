<?php
/**
 * Created by PhpStorm.
 * User: j.calabrese
 * Date: 01/07/14
 * Time: 12:03
 */

namespace Utility;


class UtilityXml {
    /**
     * @param $name
     * @param $node
     * @return null
     */
    public static function getAttribute ($name, $node)
    {
        foreach ($node->attributes() as $key => $value) {
            if ($key == $name) {
                return '' . $value;
            }
        }
        return null;
    }

    /**
     * Get the Bundle name from the filename of a class
     *
     * @param  $filename String name and path of the file we want to extract the bundle
     *
     * @return String Bundle associated to the filename
     */
    public static function getBundle($filename)
    {
        if (preg_match("#[\\]{1}[A-Za-z]{1,100}Bundle#",
            $filename,
            $bundle,
            PREG_OFFSET_CAPTURE)
        ) {
            return $bundle[0][0];
        }
        return null;
    }
} 