<?php
require('../../bootstrap.php');
if (file_exists('config.php')) {
	include('config.php');
}

acl_acceso($_SESSION['cargo'], array(1, 2, 8));
// COMPROBAMOS SI ES EL TUTOR, SI NO, ES DEL EQ. DIRECTIVO U ORIENTADOR
if (stristr($_SESSION['cargo'],'2')) {
	$_SESSION['mod_tutoria']['tutor']  = $_SESSION['mod_tutoria']['tutor'];
	$_SESSION['mod_tutoria']['unidad'] = $_SESSION['mod_tutoria']['unidad'];
}
else {
	if (isset($_POST['tutor'])) {
		$exp_tutor = explode('==>', $_POST['tutor']);
		$_SESSION['mod_tutoria']['tutor'] = trim($exp_tutor[0]);
		$_SESSION['mod_tutoria']['unidad'] = trim($exp_tutor[1]);
	}
	else {
		if (!isset($_SESSION['mod_tutoria'])) {
			header('Location:'.'tutores.php');
			exit;
		}
	}
}

// RESETEADO DE DATOS
if($_POST['delButton']=="Borrar datos"){
	$delete = mysqli_query($db_con, "delete from puestos_alumnos where unidad='".$_SESSION['mod_tutoria']['unidad']."'");
		if(! $delete) $msg_error = "El borrado de datos de puestos en el aula no se ha podido realizar. Error: ".mysqli_error($db_con);
	else $msg_success = "Los datos de puestos de alumnos de tu grupo han sido eliminados.";
}

// ESTRUCTURA DE LA CLASE, SE AJUSTA AL NUMERO DE ALUMNOS
$result = mysqli_query($db_con, "SELECT apellidos, nombre, claveal FROM alma WHERE unidad='".$_SESSION['mod_tutoria']['unidad']."' ORDER BY apellidos, nombre");
$n_alumnos = mysqli_num_rows($result);
mysqli_free_result($result);
if(isset($_POST['estructura'])){
	$mesas_estructura = $_POST['estructura'];
	}
if ($mesas_estructura==''){
	if ($n_alumnos <= 36) $mesas_estructura = '222';
	elseif ($n_alumnos > 36 && $n_alumnos <= 42) $mesas_estructura = '232';
	elseif ($n_alumnos > 42) $mesas_estructura = '242';
}
function obtenerAlumno($var_nie, $var_grupo){
	global $db_con;
	$result = mysqli_query($db_con, "SELECT `apellidos`, `nombre` FROM `alma` WHERE `unidad` = '".$var_grupo."' AND `claveal` = '".$var_nie."' ORDER BY `apellidos`, `nombre` LIMIT 1");
	if (mysqli_num_rows($result)) {
		$row = mysqli_fetch_array($result);
		mysqli_free_result($result);
		return $row['apellidos'].', '.$row['nombre'];
	}
	else {
		return '';
	}
}

// ACTUALIZAR PUESTOS
if (isset($_POST['listOfItems']) AND !isset($_POST['delButton'])){
	$result_update = mysqli_query($db_con, "UPDATE puestos_alumnos SET estructura='".$mesas_estructura."',puestos='".$_POST['listOfItems']."' WHERE unidad='".$_SESSION['mod_tutoria']['unidad']."'");
	if(! $result_update) $msg_error = "La asignación de puestos en el aula no se ha podido actualizar. Error: ".mysqli_error($db_con);
	else $msg_success = "La asignación de puestos en el aula se ha actualizado correctamente.";
}

// OBTENEMOS LOS PUESTOS, SI NO EXISTE LOS CREAMOS
$result = mysqli_query($db_con, "SELECT * FROM puestos_alumnos WHERE unidad='".$_SESSION['mod_tutoria']['unidad']."' LIMIT 1");
if (! mysqli_num_rows($result)) {
	$estructura_reg = '';
	$result_insert = mysqli_query($db_con, "INSERT INTO puestos_alumnos (unidad, puestos, estructura) VALUES ('".$_SESSION['mod_tutoria']['unidad']."', '','".$mesas_estructura."')");
	if(! $result_insert) $msg_error = "La asignación de puestos en el aula no se ha podido guardar. Error: ".mysqli_error($db_con);
}
else {
	$row = mysqli_fetch_array($result);
	$cadena_puestos = $row[1];
	$estructura_reg= $row[2];
	mysqli_free_result($result);
}
if (!isset($_POST['estructura']) && $estructura_reg <> ''){
	$mesas_estructura = $estructura_reg;
}
$matriz_puestos = explode(';', $cadena_puestos ?? '');
foreach ($matriz_puestos as $value) {
	$los_puestos = explode('|', $value);
	if ($los_puestos[0] == 'allItems') {
		$sin_puesto[] = $los_puestos[1];
	}
	else {
		$con_puesto[$los_puestos[0]] = $los_puestos[1];
	}
}
if ($mesas_estructura == '242') { $mesas_col = 9; $mesas = 48; $col_profesor = 9;}
if ($mesas_estructura == '232') { $mesas_col = 8; $mesas = 42; $col_profesor = 8;}
if ($mesas_estructura == '222') { $mesas_col = 7; $mesas = 36; $col_profesor = 7;}

include("../../menu.php");
include("menu.php");
?>
	<style class="text/css">
	table tr td {
		vertical-align: top;
	}

	table tr td.active {
		background-color: #333;
	}

	#allItems {
		width: 100%;
		border: 1px solid #ecf0f1;
	}

	#allItems p {
		background-color: #2c3e50;
		color: #fff;
		font-weight: bold;
		padding: 4px 15px;
		margin-bottom: 4px;
	}

	#allItems ul li {
		background-color: #efefef;
		padding: 5px 15px;
		margin: 5px;
		font-size: 0.8em;
		cursor: move;
	}

	#dhtmlgoodies_mainContainer table tr td div {
		border: 1px solid #ecf0f1;
		margin: 0 5px 10px 5px;
	}

	#dhtmlgoodies_mainContainer table tr td div {
		<?php if ($mesas_estructura == '242') echo 'width: 98px;'; ?>
		<?php if ($mesas_estructura == '232') echo 'width: 113px;'; ?>
		<?php if ($mesas_estructura == '222') echo 'width: 133px;'; ?>

	}

	#dhtmlgoodies_mainContainer table tr td div p {
		background-color: #2c3e50;
		color: #fff;
		font-weight: bold;
		padding: 4px 2px;
		margin-bottom: 4px;
	}

	#dhtmlgoodies_mainContainer table tr td div ul {
		margin: 0 4px 4px 4px;
		min-height: 50px;
		background-color: #efefef;
	}

	#dhtmlgoodies_mainContainer table tr td div ul li {
		height: 100%;
		cursor: move;
	}


	#dhtmlgoodies_dragDropContainer .mouseover ul {
		background-color:#E2EBED;
		border: 1px solid #3FB618;
	}

	#dragContent {
		position: absolute;
		margin-top: -280px;
		margin-left: -150px;
		width: 150px;
		height: 60px;
		font-size: 0.8em;
		z-index: 2000;
		cursor: move;
	}

	.text-sm {
		font-size: 0.7em;
	}

	.col-sm-9 {
		padding-left: 0;
		padding-right: 0;
	}

	@media print {
		html, body {
			padding: 0;
		}

		.page-header {
			margin: 5px 0;
		}

		.page-header h2 {
			font-size: 120%;
		}
		.page-header h4 {
			font-size: 100%;
		}
	}
	</style>
	<div class="container">

		<!-- TITULO DE LA PAGINA -->
		<div class="page-header">
			<h2>Tutoría de <?php echo $_SESSION['mod_tutoria']['unidad']; ?> <small>Asignación de mesas en el aula</small></h2>
			<h4 class="text-info">Tutor/a: <?php echo nomprofesor($_SESSION['mod_tutoria']['tutor']); ?></h4>
		</div>


		<!-- MENSAJES -->
		<?php if(isset($msg_success) && $msg_success): ?>
		<div class="alert alert-success" role="alert">
			<?php echo $msg_success; ?>
		</div>
		<?php endif; ?>

		<?php if(isset($msg_error) && $msg_error): ?>
		<div class="alert alert-danger" role="alert">
			<?php echo $msg_error; ?>
		</div>
		<?php endif; ?>


		<!-- SCAFFOLDING -->
		<div id="dhtmlgoodies_dragDropContainer" class="row">

			<!-- COLUMNA IZQUIERDA -->
			<div id="dhtmlgoodies_listOfItems" class="col-sm-3 hidden-print">

				<div id="allItems">
					<p>Alumnos/as</p>
					<ul class="list-unstyled">
						<?php $result = mysqli_query($db_con, "SELECT apellidos, nombre, claveal FROM alma WHERE unidad='".$_SESSION['mod_tutoria']['unidad']."' ORDER BY apellidos, nombre"); ?>
						<?php while ($row = mysqli_fetch_array($result)): ?>
						<?php if (! in_array($row['claveal'], $con_puesto)): ?>
					  <li id="<?php echo $row['claveal']; ?>"><?php echo $row['apellidos'].', '.$row['nombre']; ?></li>
					  <?php endif; ?>
					  <?php endwhile; ?>
					</ul>
				</div>

				<ul id="dragContent" class="list-unstyled"></ul>

			</div><!-- /.col-sm-3 -->


			<!-- COLUMNA DERECHA -->
			<div id="dhtmlgoodies_mainContainer" class="col-sm-9">

				<form class="hidden-print" action="" method="post" style="margin-bottom: 10px;">
					<h5 style="font-weight: bold; display: inline-block; margin-right: 10px;">Tipo de disposición: </h5>

					<label class="radio-inline">
						<input type="radio" name="estructura" value='222' onchange="submit()" <?php echo ($mesas_estructura == '222') ? 'checked' : ''; ?>> 36 mesas
					</label>
					<label class="radio-inline">
						<input type="radio" name="estructura" value='232' onchange="submit()" <?php echo ($mesas_estructura == '232') ? 'checked' : ''; ?>> 42 mesas
					</label>
					<label class="radio-inline">
						<input type="radio" name="estructura" value='242' onchange="submit()" <?php echo ($mesas_estructura == '242') ? 'checked' : ''; ?>> 48 mesas
					</label>
				</form>

				<table>
					<?php for ($i = 1; $i < 7; $i++): ?>
					<tr>
						<?php for ($j = 1; $j < $mesas_col; $j++): ?>
						<td>
							<div><p class="text-center">Mesa <?php echo $mesas; ?></p>
								<ul id="<?php echo $mesas; ?>" class="list-unstyled text-sm">
									<?php if (isset($con_puesto[$mesas])): ?>
										<li id="<?php echo $con_puesto[$mesas]; ?>"><?php echo obtenerAlumno($con_puesto[$mesas], $_SESSION['mod_tutoria']['unidad']); ?></li>
									<?php endif; ?>
								</ul>
							</div>
						</td>
						<?php if (($mesas_estructura == '222' && ($j == 2 || $j == 4)) || ($mesas_estructura == '232' && ($j == 2 || $j == 5))  || ($mesas_estructura == '242' && ($j == 3 || $j == 5))): ?>
						<td class="text-center active">|</td>
						<?php endif; ?>
						<?php $mesas--; ?>
						<?php endfor; ?>
					</tr>
					<?php endfor; ?>
					<tr>
						<td colspan="<?php echo $col_profesor; ?>">
							<br><p id="dragDropIndicator" class="text-info hidden-print">Arrastre un alumno/a a la mesa correspondiente</p>
						</td>
						<td class="text-center">
							<div>
								<p>Profesor/a</p>
								<br><br><br>
							</div>
						</td>
					</tr>
				</table>

			</div><!-- /.col-sm-9 -->

		</div><!-- /.row -->

		<br>

		<div class="row">

			<div class="col-sm-12">

				<div class="hidden-print">
					<form id="myForm" name="myForm" method="post" action="" onsubmit="saveDragDropNodes()">
						<input type="hidden" name="listOfItems" value="">
						<button type="submit" class="btn btn-primary" name="saveButton">Guardar cambios</button>
						<input type="submit" class="btn btn-info" name="delButton" value="Borrar datos"/>
						<a href="#" class="btn btn-default" onclick="print();">Imprimir</a>
						<a class="btn btn-default" href="index.php">Volver</a>
					</form>
				</div>

			</div><!-- /col-sm-12 -->

		</div><!-- /.row -->

	</div><!-- /.container -->

<?php include("../../pie.php"); ?>
<script type="text/javascript">
	/************************************************************************************************************
	(C) www.dhtmlgoodies.com, November 2005
	Update log:
	December 20th, 2005 : Version 1.1: Added support for rectangle indicating where object will be dropped
	January 11th, 2006: Support for cloning, i.e. "copy & paste" items instead of "cut & paste"
	January 18th, 2006: Allowing multiple instances to be dragged to same box(applies to "cloning mode")
	This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.
		Terms of use:
	You are free to use this script as long as the copyright message is kept intact. However, you may not
	redistribute, sell or repost it without our permission.
	Thank you!
	www.dhtmlgoodies.com
	Alf Magne Kalleland
	************************************************************************************************************/
	/* VARIABLES YOU COULD MODIFY */
	<?php if ($mesas_estructura=='242'): ?>
	var boxSizeArray = [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1];
	<?php elseif ($mesas_estructura=='232'): ?>
	var boxSizeArray = [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1];
	<?php elseif ($mesas_estructura=='222'): ?>
	var boxSizeArray = [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1];
	<?php endif; ?>
	// Array indicating how many items  there is rooom for in the right column ULs
	var verticalSpaceBetweenListItems = 3;	// Pixels space between one <li> and next
																					// Same value or higher as margin bottom in CSS for #dhtmlgoodies_dragDropContainer ul li,#dragContent li
	var indicateDestionationByUseOfArrow = false;	// Display arrow to indicate where object will be dropped(false = use rectangle)
	var cloneSourceItems = false;	// Items picked from main container will be cloned(i.e. "copy" instead of "cut").
	var cloneAllowDuplicates = false;	// Allow multiple instances of an item inside a small box(example: drag Student 1 to team A twice
	/* END VARIABLES YOU COULD MODIFY */
	var dragDropTopContainer = false;
	var dragTimer = -1;
	var dragContentObj = false;
	var contentToBeDragged = false;	// Reference to dragged <li>
	var contentToBeDragged_src = false;	// Reference to parent of <li> before drag started
	var contentToBeDragged_next = false; 	// Reference to next sibling of <li> to be dragged
	var destinationObj = false;	// Reference to <UL> or <LI> where element is dropped.
	var dragDropIndicator = false;	// Reference to small arrow indicating where items will be dropped
	var ulPositionArray = [];
	var mouseoverObj = false;	// Reference to highlighted DIV
	var MSIE = navigator.userAgent.indexOf('MSIE') >= 0;
	var navigatorVersion = navigator.appVersion.replace(/.*?MSIE (\d\.\d).*/g,'$1')/1;
	var arrow_offsetX = -5;	// Offset X - position of small arrow
	var arrow_offsetY = 0;	// Offset Y - position of small arrow
	if(!MSIE || navigatorVersion > 6){
		arrow_offsetX = -6;	// Firefox - offset X small arrow
		arrow_offsetY = -13; // Firefox - offset Y small arrow
	}
	var indicateDestinationBox = false;
	function getTopPos(inputObj)
	{
	  var returnValue = inputObj.offsetTop;
	  while((inputObj = inputObj.offsetParent) != null){
	  	if(inputObj.tagName!='HTML')
              returnValue += inputObj.offsetTop;
	  }
	  return returnValue;
	}
	function getLeftPos(inputObj)
	{
	  var returnValue = inputObj.offsetLeft;
	  while((inputObj = inputObj.offsetParent) != null){
	  	if(inputObj.tagName!='HTML')
              returnValue += inputObj.offsetLeft;
	  }
	  return returnValue;
	}
	function cancelEvent()
	{
		return false;
	}
	function initDrag(e)	// Mouse button is pressed down on a LI
	{
		if(document.all)e = event;
		var st = Math.max(document.body.scrollTop,document.documentElement.scrollTop);
		var sl = Math.max(document.body.scrollLeft,document.documentElement.scrollLeft);
		dragTimer = 0;
		dragContentObj.style.left = e.clientX + sl + 'px';
		dragContentObj.style.top = e.clientY + st + 'px';
		contentToBeDragged = this;
		contentToBeDragged_src = this.parentNode;
		contentToBeDragged_next = false;
		if(this.nextSibling){
			contentToBeDragged_next = this.nextSibling;
			if(!this.tagName && contentToBeDragged_next.nextSibling)contentToBeDragged_next = contentToBeDragged_next.nextSibling;
		}
		timerDrag();
		return false;
	}
	function timerDrag()
	{
		if(dragTimer>=0 && dragTimer<10){
			dragTimer++;
			setTimeout('timerDrag()',10);
			return;
		}
		if(dragTimer==10){
			if(cloneSourceItems && contentToBeDragged.parentNode.id=='allItems'){
				newItem = contentToBeDragged.cloneNode(true);
				newItem.onmousedown = contentToBeDragged.onmousedown;
				contentToBeDragged = newItem;
			}
			dragContentObj.style.display='block';
			dragContentObj.appendChild(contentToBeDragged);
		}
	}
	function moveDragContent(e)
	{
		if(dragTimer<10){
			if(contentToBeDragged){
				if(contentToBeDragged_next){
					contentToBeDragged_src.insertBefore(contentToBeDragged,contentToBeDragged_next);
				}else{
					contentToBeDragged_src.appendChild(contentToBeDragged);
				}
			}
			return;
		}
		if(document.all)e = event;
		var st = Math.max(document.body.scrollTop,document.documentElement.scrollTop);
		var sl = Math.max(document.body.scrollLeft,document.documentElement.scrollLeft);
		dragContentObj.style.left = e.clientX + sl + 'px';
		dragContentObj.style.top = e.clientY + st + 'px';
		if(mouseoverObj)mouseoverObj.className='';
		destinationObj = false;
		dragDropIndicator.style.display='none';
		if(indicateDestinationBox)indicateDestinationBox.style.display='none';
		var x = e.clientX + sl;
		var y = e.clientY + st;
		var width = dragContentObj.offsetWidth;
		var height = dragContentObj.offsetHeight;
		var tmpOffsetX = arrow_offsetX;
		var tmpOffsetY = arrow_offsetY;
		for(var no=0;no<ulPositionArray.length;no++){
			var ul_leftPos = ulPositionArray[no]['left'];
			var ul_topPos = ulPositionArray[no]['top'];
			var ul_height = ulPositionArray[no]['height'];
			var ul_width = ulPositionArray[no]['width'];
			if((x+width) > ul_leftPos && x<(ul_leftPos + ul_width) && (y+height)> ul_topPos && y<(ul_topPos + ul_height)){
				var noExisting = ulPositionArray[no]['obj'].getElementsByTagName('LI').length;
				if(indicateDestinationBox && indicateDestinationBox.parentNode==ulPositionArray[no]['obj'])noExisting--;
				if(noExisting<boxSizeArray[no-1] || no==0){
					dragDropIndicator.style.left = ul_leftPos + tmpOffsetX + 'px';
					var subLi = ulPositionArray[no]['obj'].getElementsByTagName('LI');
					var clonedItemAllreadyAdded = false;
					if(cloneSourceItems && !cloneAllowDuplicates){
						for(var liIndex=0;liIndex<subLi.length;liIndex++){
							if(contentToBeDragged.id == subLi[liIndex].id)clonedItemAllreadyAdded = true;
						}
						if(clonedItemAllreadyAdded)continue;
					}
					for(var liIndex=0;liIndex<subLi.length;liIndex++){
						var tmpTop = getTopPos(subLi[liIndex]);
						if(!indicateDestionationByUseOfArrow){
							if(y<tmpTop){
								destinationObj = subLi[liIndex];
								indicateDestinationBox.style.display='block';
								subLi[liIndex].parentNode.insertBefore(indicateDestinationBox,subLi[liIndex]);
								break;
							}
						}else{
							if(y<tmpTop){
								destinationObj = subLi[liIndex];
								dragDropIndicator.style.top = tmpTop + tmpOffsetY - Math.round(dragDropIndicator.clientHeight/2) + 'px';
								dragDropIndicator.style.display='block';
								break;
							}
						}
					}
					if(!indicateDestionationByUseOfArrow){
						if(indicateDestinationBox.style.display=='none'){
							indicateDestinationBox.style.display='block';
							ulPositionArray[no]['obj'].appendChild(indicateDestinationBox);
						}
					}else{
						if(subLi.length>0 && dragDropIndicator.style.display=='none'){
							dragDropIndicator.style.top = getTopPos(subLi[subLi.length-1]) + subLi[subLi.length-1].offsetHeight + tmpOffsetY + 'px';
							dragDropIndicator.style.display='block';
						}
						if(subLi.length==0){
							dragDropIndicator.style.top = ul_topPos + arrow_offsetY + 'px'
							dragDropIndicator.style.display='block';
						}
					}
					if(!destinationObj)destinationObj = ulPositionArray[no]['obj'];
					mouseoverObj = ulPositionArray[no]['obj'].parentNode;
					mouseoverObj.className='mouseover';
					return;
				}
			}
		}
	}
	/* End dragging
	Put <LI> into a destination or back to where it came from.
	*/
	function dragDropEnd(e)
	{
		if(dragTimer==-1)return;
		if(dragTimer<10){
			dragTimer = -1;
			return;
		}
		dragTimer = -1;
		if(document.all)e = event;
		if(cloneSourceItems && (!destinationObj || (destinationObj && (destinationObj.id=='allItems' || destinationObj.parentNode.id=='allItems')))){
			contentToBeDragged.parentNode.removeChild(contentToBeDragged);
		}else{
			if(destinationObj){
				if(destinationObj.tagName=='UL'){
					destinationObj.appendChild(contentToBeDragged);
				}else{
					destinationObj.parentNode.insertBefore(contentToBeDragged,destinationObj);
				}
				mouseoverObj.className='';
				destinationObj = false;
				dragDropIndicator.style.display='none';
				if(indicateDestinationBox){
					indicateDestinationBox.style.display='none';
					document.body.appendChild(indicateDestinationBox);
				}
				contentToBeDragged = false;
				return;
			}
			if(contentToBeDragged_next){
				contentToBeDragged_src.insertBefore(contentToBeDragged,contentToBeDragged_next);
			}else{
				contentToBeDragged_src.appendChild(contentToBeDragged);
			}
		}
		contentToBeDragged = false;
		dragDropIndicator.style.display='none';
		if(indicateDestinationBox){
			indicateDestinationBox.style.display='none';
			document.body.appendChild(indicateDestinationBox);
		}
		mouseoverObj = false;
	}
	/*
	Preparing data to be saved
	*/
	function saveDragDropNodes()
	{
		var saveString = "";
		var uls = dragDropTopContainer.getElementsByTagName('ul');
		for(var no=0;no<uls.length;no++){	// LOoping through all <ul>
			var lis = uls[no].getElementsByTagName('li');
			for(var no2=0;no2<lis.length;no2++){
				if(saveString.length>0)saveString = saveString + ";";
				saveString = saveString + uls[no].id + '|' + lis[no2].id;
			}
		}
		saveString = saveString + ";";
		document.forms['myForm'].listOfItems.value = saveString;
		document.getElementById('saveContent').innerHTML = '<h1>Ready to save these nodes:</h1> ' + saveString.replace(/;/g,';<br>') + '<p>Format: ID of ul |(pipe) ID of li;(semicolon)</p><p>You can put these values into a hidden form fields, post it to the server and explode the submitted value there</p>';
	}
	function initDragDropScript()
	{
		dragContentObj = document.getElementById('dragContent');
		dragDropIndicator = document.getElementById('dragDropIndicator');
		dragDropTopContainer = document.getElementById('dhtmlgoodies_dragDropContainer');
		document.documentElement.onselectstart = cancelEvent;
		var listItems = dragDropTopContainer.getElementsByTagName('LI');	// Get array containing all <LI>
		var itemHeight = false;
		for(var no=0;no<listItems.length;no++){
			listItems[no].onmousedown = initDrag;
			listItems[no].onselectstart = cancelEvent;
			if(!itemHeight)itemHeight = listItems[no].offsetHeight;
			if(MSIE && navigatorVersion/1<6){
				listItems[no].style.cursor='hand';
			}
		}
		var mainContainer = document.getElementById('dhtmlgoodies_mainContainer');
		var uls = mainContainer.getElementsByTagName('UL');
		itemHeight = itemHeight + verticalSpaceBetweenListItems;
		for(var no=0;no<uls.length;no++){
			uls[no].style.height = itemHeight * boxSizeArray[no]  + 'px';
		}
		var leftContainer = document.getElementById('dhtmlgoodies_listOfItems');
		var itemBox = leftContainer.getElementsByTagName('UL')[0];
		document.documentElement.onmousemove = moveDragContent;	// Mouse move event - moving draggable div
		document.documentElement.onmouseup = dragDropEnd;	// Mouse move event - moving draggable div
		var ulArray = dragDropTopContainer.getElementsByTagName('UL');
		for(var no=0;no<ulArray.length;no++){
			ulPositionArray[no] = new Array();
			ulPositionArray[no]['left'] = getLeftPos(ulArray[no]);
			ulPositionArray[no]['top'] = getTopPos(ulArray[no]);
			ulPositionArray[no]['width'] = ulArray[no].offsetWidth;
			ulPositionArray[no]['height'] = ulArray[no].clientHeight;
			ulPositionArray[no]['obj'] = ulArray[no];
		}
		if(!indicateDestionationByUseOfArrow){
			indicateDestinationBox = document.createElement('LI');
			indicateDestinationBox.id = 'indicateDestination';
			indicateDestinationBox.style.display='none';
			document.body.appendChild(indicateDestinationBox);
		}
	}
	window.onload = initDragDropScript;
	</script>
