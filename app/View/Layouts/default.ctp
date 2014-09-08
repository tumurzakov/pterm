<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('foundation.min');
		echo $this->Html->css('main');

        echo $this->Html->script('vendor/modernizr');
        echo $this->Html->script('vendor/jquery');
        echo $this->Html->script('vendor/fastclick');
        echo $this->Html->script('foundation.min');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
	<div id="container">
		<div id="header">
            <nav class="top-bar" data-topbar role="navigation">
                <ul class="title-area">
                    <li class="name">
                        <h1><a href="#">Terminal</a></h1>
                    </li>

                    <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
                </ul>

                <section class="top-bar-section">
                    <ul class="left">
                        <li><a href="#">Correction</a></li>

                        <li class="has-dropdown">
                            <a href="#">Reports</a>
                            <ul class="dropdown">
                                <li><a href="#">Summary</a></li>
                                <li><a href="#">Payments</a></li>
                                <li><a href="#">Events</a></li>
                            </ul>
                        </li>

                        <li class="has-dropdown">
                            <a href="#">Config</a>
                            <ul class="dropdown">
                                <li><a href="#">Variables</a></li>
                                <li><a href="#">Terminals</a></li>
                                <li><a href="#">Neotech requests</a></li>
                                <li><a href="#">Neotech responses</a></li>
                            </ul>
                        </li>
                    </ul>
                </section>
            </nav>
		</div>
		<div id="content" class='twelve columns'>

			<?php echo $this->Session->flash(); ?>

			<?php echo $this->fetch('content'); ?>

		</div>
		<div id="footer">
		</div>

        <script type='text/javascript'>
            $(document).foundation();
        </script>
	</div>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
