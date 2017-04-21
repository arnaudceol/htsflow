<?php 
/*
 * Copyright 2015-2016 Fondazione Istituto Italiano di Tecnologia.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

?><head>
<title>HTS-flow <?php echo $_SESSION["hf_user_group"] ; ?></title>
<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">

<style type="text/css">
@import url('styles/page.css');
</style>
<!-- <script src="//code.jquery.com/jquery-1.11.3.min.js"></script> -->
<script src="libs/jquery-1.11.3.min.js"></script>
<script src="libs/jquery-ui.min.js"></script>
<script type="text/javascript" src="libs/JSfunctions.js"></script>




<!-- <script type="text/javascript" src="libs/TableFilter/tablefilter.js"></script> -->
<!--   <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"> -->
<!--   <script src="//code.jquery.com/jquery-1.10.2.js"></script> -->
<!--   <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>   -->
  <script>
  $(function() {
	  if ($('#tabs').length){
    	$( "#tabs" ).tabs();
	  }
  });
  </script>
</head>
