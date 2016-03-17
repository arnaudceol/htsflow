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
session_start();

?>
<html>
<body>
<?php
$paths = $_POST["paths"];

foreach (explode("\n", $paths) as $path) {
    $path = trim($path);
    
    if ($path != "") {
        if (file_exists($path) == FALSE) {
            echo "<b style=\"color:red\">Path: " . $path . " not found.</b><br/>";
        } else {
            $perms = fileperms($path);
            
            $allCanRead = (($perms & 0x0004) ? TRUE : FALSE);
            $groupCanRead = (($perms & 0x0020) ? TRUE : FALSE);
            $ownerCanRead = (($perms & 0x0100) ? TRUE : FALSE);
            
            $userCanRead = $allCanRead;
            if ($allCanRead) {
                $userCanRead = TRUE;
            } else {
                $pathInfo = stat($path);
                
                $userInfo = posix_getpwnam($_SESSION["hf_user_name"]);
                $userUnixId = $userInfo["uid"];                
                $pathOwnerId = $pathInfo["uid"];
                
                // Is user the owner ?
                if ($ownerCanRead && $userUnixId == $pathOwnerId) {
                    $userCanRead = TRUE;
                } else 
                    if ($groupCanRead) {
                        // check group
                        $pathGroupId = $pathInfo["gid"];
                        $groupInfo = posix_getgrgid($pathGroupId);
                        foreach ($groupInfo["members"] as $key => $value) {
                            if ($key == $userUnixId) {
                                $userCanRead = TRUE;
                            }
                        }
                    }
            }
            
            if ($userCanRead == FALSE) {
                echo "<b style=\"color:red\">You are not allowed to acess path: " . $path . ".</b><br/>";
            } else {

                echo "Path: " . $path;
                if (is_readable ( $path )) {
                    echo "<ul>";
                    if (is_dir($path)) {
                        foreach (scandir($path) as $file) {
                            if ($file != "." && $file != "..") {
                                // Create a good looking title, with space and first letter uppercase
                                echo "<li>" . $file . "</li>";
                            }
                        }
                    }
                    echo "</ul>";
                } else {
                    echo "<b style=\"color:red\">HTS-flow web server cannot access this directory. You should either 
                        give permission to HTS-flow to read the directory if you want to check its content.
                        If you are sure about the path and that you have the permission to access it from the scripts, you and add it anyway.</b><br/>";
                }
            }
        }
    }
}

?>
</body>
</html>