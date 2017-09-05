<!DOCTYPE html>
<html>
<head>
  <title>SimSat</title>
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.1.0.min.js"></script>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php

# If submitted, set new values
if( ! empty( $_POST ) ){
  if(
    is_numeric( $_POST['delay'] ) &&
    is_numeric( $_POST['delayPlusMinus'] ) &&
    is_numeric( $_POST['loss'] ) &&
    is_numeric( $_POST['lossPlusMinus'] ) &&
    is_numeric( $_POST['corrupt'] ) &&
    is_numeric( $_POST['rate'] )
  ){
    # Delay, Loss, and Corrupt are halved as they occur twice (in & out).
    $delayVar = "delay ".sprintf( "%f", ( (float) $_POST['delay'] )/2)."ms ".sprintf( "%f", ( (float) $_POST['delayPlusMinus'])/2)."ms distribution normal";
    $lossVar = "loss ".sprintf( "%f", ( (float) $_POST['loss'] )/2)."% ".sprintf( "%f", ( (float) $_POST['lossPlusMinus'])/2)."%";
    $corruptVar = "corrupt ".sprintf( "%f", ( (float) $_POST['corrupt'] )/2)."%";
    $rateVar = $_POST['rate'].$_POST['rateUnit'];

    $simsatPath = "/usr/local/SimSat/SimSat";
    $cmdVarsStr = "DELAY=\"$delayVar\" LOSS=\"$lossVar\" CORRUPT=\"$corruptVar\" RATE=\"$rateVar\" ";

    $result = exec( "sudo $simsatPath stop", $output );
    $result = exec( "sudo $cmdVarsStr $simsatPath start", $output );
  }
  if( isset( $_POST['delete_config'] ) && 'delete_config' == $_POST['delete_config'] ){
    exec( "sudo $simsatPath unsave" );
    $notifications = 'Configuration has been saved to disk.';
  }
  if( isset( $_POST['start'] ) && 'start' == $_POST['start'] ){
    exec( "sudo $simsatPath start" );
    $notifications = 'SimSat has been started.';
  }
  if( isset( $_POST['stop'] ) && 'stop' == $_POST['stop'] ){
    exec( "sudo $simsatPath stop" );
    $notifications = 'SimSat has been stopped.';
  }
}

# Defaults

$cfg['delay']          = "600";
$cfg['delayPlusMinus'] = "10";
$cfg['loss']           = "1";
$cfg['lossPlusMinus']  = "25";
$cfg['corrupt']        = "1";
$cfg['rate']           = "15";
$cfg['rateUnit']      = "mbps";

# READ IN EXISTING VALUES

$out = exec( "/usr/local/SimSat/SimSat status", $output, $status );

foreach( $output as $line ){
  # Read in rate setting
  if( "class hfsc 1:1 parent" == substr( $line, 0, 21 ) ){
    $tmp = explode( " ", $line );
    $arr = preg_split( '/(?<=[0-9])(?=[a-z]+)/i', array_pop( $tmp ) );
    $cfg['rate'] = $arr[0];
    $cfg['rateUnit'] = strtolower( $arr[1] );
  }
  # Read in delay, loss, and corrupt settings
  if( "qdisc netem 10: parent 1:1 limit" == substr( $line, 0, 32 ) ){
    $tmp = explode( " ", $line );
    
    # Delay, Loss, and Corrupt are halved as they occur twice (in & out).
    # The displayed values are the totals (human readable), not the actual settings
    $cfg['delay'] = ( (int) trim( $tmp[8], 'ms' ) ) * 2;
    $cfg['delayPlusMinus'] = ( (int) trim( $tmp[10], 'ms' ) ) * 2;
    $cfg['loss'] = ( (float) trim( $tmp[12], '%' ) ) * 2;
    $cfg['lossPlusMinus'] = ( (float) trim( $tmp[13], '%' ) ) * 2;
    $cfg['corrupt'] = ( (float) trim( $tmp[15], '%' ) ) *2;
  }
}


if( ! empty( $_POST ) && isset( $_POST['save_config'] ) && 'save_config' == $_POST['save_config'] ){
    # Delay, Loss, and Corrupt are halved as they occur twice (in & out).
    $delayVar = "delay ".sprintf( "%f", ( (float) $cfg['delay'] )/2)."ms ".sprintf( "%f", ( (float) $cfg['delayPlusMinus'])/2)."ms distribution normal";
    $lossVar = "loss ".sprintf( "%f", ( (float) $cfg['loss'] )/2)."% ".sprintf( "%f", ( (float) $cfg['lossPlusMinus'])/2)."%";
    $corruptVar = "corrupt ".sprintf( "%f", ( (float) $cfg['corrupt'] )/2)."%";
    $rateVar = $cfg['rate'].$cfg['rateUnit'];

    $simsatPath = "/usr/local/SimSat/SimSat";
    $cmdVarsStr = "DELAY=\"$delayVar\" LOSS=\"$lossVar\" CORRUPT=\"$corruptVar\" RATE=\"$rateVar\" ";

    $result = exec( "sudo $cmdVarsStr $simsatPath save", $output );
    $notifications = 'Configuration has been saved to disk.';
}
?>

<div class="container-fluid">
<?php if( isset( $notifications ) ){
    echo '<div class="row">';
    echo '<div class="col-md-12 alert alert-warning">';
    echo $notifications; }
    echo '</div>';
    echo '</div>';
?>
  <div class="row">
    <form method="post" role="form">
     <div class="form-group">
      <div class="col-md-1">
        <label for="delay">Delay</label>
        <input class="form-control" type="text" name="delay" value="<?php echo $cfg['delay']; ?>" /> ms
      </div>
      <div class="col-md-1">
        <label for="delayPlusMinus">Delay +/-</label>
        <input class="form-control" type="text" name="delayPlusMinus" value="<?php echo $cfg['delayPlusMinus']; ?>" /> ms
      </div>
      <div class="col-md-1">
        <label for="loss">Loss</label>
        <input class="form-control" type="text" name="loss" value="<?php echo sprintf( "%f", $cfg['loss'] ); ?>" /> %
      </div>
      <div class="col-md-1">
        <label for="lossPlusMinus">Loss +/-</label>
        <input class="form-control" type="text" name="lossPlusMinus" value="<?php echo $cfg['lossPlusMinus']; ?>" /> %
      </div>
      <div class="col-md-2">
        <label for="corrupt">Corrupt</label>
        <input class="form-control" type="text" name="corrupt" value="<?php echo sprintf( "%f", $cfg['corrupt'] ); ?>" /> %
      </div>
      <div class="col-md-1">
        <label for="rate">Rate</label>
        <input class="form-control" type="text" name="rate" value="<?php echo $cfg['rate']; ?>" />
      </div>
      <div class="col-md-1">
        <label for="rateInput">Rate units</label>
        <select class="form-control" name="rateUnit">
          <option value="kbps"<?php echo ( $cfg['rateUnit'] == "kbps" ? " selected='selected'" : ""); ?>>kbps (Kilobytes per second)</option>
          <option value="mbps"<?php echo ( $cfg['rateUnit'] == "mbps" ? " selected='selected'" : ""); ?>>mbps (Megabytes per second)</option>
          <option value="kbit"<?php echo ( $cfg['rateUnit'] == "kbit" ? " selected='selected'" : ""); ?>>kbit (Kilobits per second)</option>
          <option value="mbit"<?php echo ( $cfg['rateUnit'] == "mbit" ? " selected='selected'" : ""); ?>>mbit (Megabits per second)</option>
          <option value="bps"<?php echo ( $cfg['rateUnit'] == "bps" ? " selected='selected'" : ""); ?>>bps (Bytes per second)</option>
        </select>
      </div>
      <div class="col-md-1">
        <label for=""></label>
        <input class="form-control" type="submit" value="Update" />
      </div>
     </div>
    </form>
    <form method="post" role="form">
     <div class="form-group">
      <div class="col-md-1">
        <label for=""></label>
        <input name='save_config' type='hidden' value='save_config' />
        <input class="form-control" type="submit" value="Save Config to Disk" />
      </div>
     </div>
    </form>
    <form method="post" role="form">
     <div class="form-group">
      <div class="col-md-1">
        <label for=""></label>
        <input name='delete_config' type='hidden' value='delete_config' />
        <input class="form-control" type="submit" value="Delete Config on Disk" />
      </div>
     </div>
    </form>
    <form method="post" role="form">
     <div class="form-group">
      <div class="col-md-1">
        <label for=""></label>
        <input name='start' type='hidden' value='start' />
        <input class="form-control" type="submit" value="Start" />
      </div>
     </div>
    </form>
    <form method="post" role="form">
     <div class="form-group">
      <div class="col-md-1">
        <label for=""></label>
        <input name='stop' type='hidden' value='stop' />
        <input class="form-control" type="submit" value="Stop" />
      </div>
     </div>
    </form>
  
  </div>
</div>

</body>
</html>
