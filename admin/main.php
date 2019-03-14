<?php
if (!isset($_SESSION['logged']))
{
  header("Refresh:0; url=index.php");
  exit;
}
// zpracovani post
if (isset($_POST['logout']))
{ 
  unset($_SESSION['logged']);
  session_destroy();
  header("Refresh:0; url=index.php");
  exit;
}
?>
<!--
<script type="text/javascript" charset="UTF-8" src="resources/javascripts/ReservationMover.js"></script>
<script type="text/javascript" charset="UTF-8" src="resources/javascripts/AdminMainPage.js"></script>
<script type="text/javascript" charset="UTF-8" src="resources/javascripts/ReservationCaledar.js"></script>
<script type="text/javascript" charset="UTF-8" src="resources/javascripts/NewReservations.js"></script>
-->
<script type="text/javascript" charset="UTF-8" src="resources/javascripts/ReservationAdmin.min.js"></script>
    
<div class="adm-body">  
  <form method="post">
    <div class="adm-topheader">
      <div class="adm-topheader-pagecaption">Správce rezervací</div>
      <input class="adm-bt-logout" type="submit" name="logout" value="odhlásit"/>
    </div>
  </form>
  <div class="adm-appbody">
    <div class="adm-bodytable">
      <div class="adm-leftpanel">
        <div class="adm-leftpanel-intable">
          <div style="display: table-row;">
            <div class="adm-calendarframe"><div id="datepicker"></div></div>
          </div>
          <div class="adm-calend">
            <div class="adm-calend-free">
              <div class="adm-calend-free-icon"></div>
              <div class="adm-calend-free-caption">- volné termíny</div>              
            </div>
            <div class="adm-calend-invisible">
              <div class="adm-calend-invisible-icon"></div>
              <div class="adm-calend-invisible-caption">- skryté termíny</div>
            </div>
            <div class="adm-calend-res">
              <div class="adm-calend-res-icon"></div>
              <div class="adm-calend-res-caption">- rezervace</div>
            </div>
          </div>
          <div style="display: table-row;">
            <div class="adm-dayterms-frame">
              <div class="adm-dayterms-caption">Termíny
              </div>
              <div class="adm-dayterms-tools">
                <div class="adm-dayterms-tools-newbt">Přidat</div>
              </div>
              
              <div class="adm-dayterms-view"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="adm-content">
        <!--
                <div class="adm-dayterms-newbt"><img src="../images/newbt.png"></div>
        <div class="adm-day-tools"></div>
        -->
        <div class="adm-upconn">
          <div>
            <div class="adm-day-header">
              <div class="adm-search">
                Vyhledat rezervace: 
                <div class="search-textbox">
                  <input type="text"/>
                  <img src="../images/SearchGlass.png"/>
                </div>
              </div>
            </div>
            <div class="adm-day-conn"></div>             
          </div>
          <div class="adm-newresconn">
             <div class="adm-newresconn-caption"> Nové rezervace: <span class="newrescount">0</span></div>
             <div class="adm-newresconn-conn"></div>
          </div>
        </div>
        <div class="adm-freeresconn">
          <div class="freeresconn-caption"> Volné rezervace: <span class="rescount"></span>
            <div class="maxminbt">
              <img src="../images/Up.png"/>
            </div>
          </div>
          <div class="freeresconn-conn"></div>
        </div>
      </div>
    </div>
  </div>
</div>

