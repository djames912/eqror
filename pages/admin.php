<?php
/*
 * This file is nested in a container_16
 */
?>

<div class="grid_8">
    <input type="text" id="position" name="position"> <button id="submitpos" onClick="addPosition(); ">Add Position</button>
    <h1>Positions in the database</h1>
    <span id="positions"></span>
</div>
<div class="grid_8">
    Surname: <input type="text" id="mbrsur" name="mbrsur">
    <br>
    Given Name: <input type="text" id="mbrgivn" name="mbrgivn">
    <br>
    Middle: <input type="text" id="mbrmidl" name="mbrmidl">
    <br>
    Suffix: <input type="text" id="mbrsufx" name="mbrsufx">
    <br>
    Prefers: <input type="text" id="mbrpref" name="mbrpref">
    <br>
    <button id="submitmem" onClick="addMember(); ">Add Member</button>
    <h1>Members in the database</h1>
    <span id="members"></span>
</div>