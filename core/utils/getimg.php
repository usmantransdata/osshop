<?php

/**
  In case you need to extend current generator class:
    - create some alternative file;
    - edit htaccess file and replace core/utils/getimg.php with your custom handler;
    - add here function "getGeneratorInstanceName()" which returns name of your generator class;
    - implement class and required methods which extends "oxdynimggenerator" class
    e.g.:

      file name "testgenerator.php"

      function getGeneratorInstanceName()
      {
          return "testImageGenerator";
      }
      include_once "oxdynimggenerator.php";
      class testImageGenerator extends oxdynimggenerator.php {...}
*/

// including generator class
require_once "../oxdynimggenerator.php";

// rendering requested image
oxDynImgGenerator::getInstance()->outputImage();