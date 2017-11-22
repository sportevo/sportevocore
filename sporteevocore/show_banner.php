<?php
/*
  show_banner.php, v 1.0, 2003/07/24
  Contribution for use with Francesco Rossi, Open Source E-Commerce Solutions, http://www.ontc.eu.

  Copyright (c) 2015/3 Pablo D'Ambrosio, pablo@customware.com.ar

  Released under the GNU General Public License
*/

require('includes/application_top.php');

// check parameters: banner (id) or group
if (isset($_GET['banner'])) {
  $banner = $_GET['banner'];
} else {
  $group = (isset($_GET['group']) ? $_GET['group'] : '468x50');
  $banner = tep_banner_exists('dynamic', $group);
}

// if there is banner... show it
if (isset($banner)) {
  echo tep_display_banner('static', $banner);
}

?>
