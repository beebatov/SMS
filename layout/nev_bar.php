<!--
===============================
  Navbar
===============================
-->


<?php 

$name=$login_user['uname'];

?>
 <div class="navbar navbar-default" role="navigation" style="position: auto; border-width: 0px 0px 1px 0px; border-color: rgba(0,0,0,0.2); padding: 10px;" >

        <div class="navbar-headerr"  style="position: auto; border-width: 0px;">

        <ul class="nav navbar-nav navbar-left">  
          <span onclick="action_side_bar()" class="sidebar-toggle-action">
          <button  class="btn_nav_toggle"><i class="fa fa-bars" id="icon_div"></i></button>
          </span>

          <a class="" href="index.php"><span class="navbar-brand"  style="color:var(--font-color)"><font class="logo_title"> TechSerm Education Software</font></span></a>
            <a href="add_student.php">
              <button class="btn_tab" style="margin-left: 15px;"><i class="fa fa-home"></i> Add Student</button></a>
            <button class="btn_tab"><i class="fa fa-home"></i> View Student</button>
            <button class="btn_tab"><i class="fa fa-home"></i> Payment Receive</button>
          
        </ul>
        
               

              
        </div>
</div>

                
       
<script type="text/javascript">
  function action_side_bar(){
    div=document.getElementById('content');
    icon_div=document.getElementById('icon_div');
    class_name=div.className;
    if(class_name=='content_with_sidebar'){
      div.className = 'content';
      icon_div.className='fa fa-bars';
    }
    else{
      div.className = 'content_with_sidebar';
      icon_div.className='fa fa-times';
    }
  }
</script>
<link rel="stylesheet" type="text/css" href="style/css/nav_bar.css">

        </div>
  </div>
