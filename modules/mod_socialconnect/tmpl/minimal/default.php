<?php
/**
 * @version     1.8.x
 * @package     SocialConnect
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license     http://www.joomlaworks.net/license
 */

defined('_JEXEC') or die; ?>

<?php JHTML::_('behavior.tooltip'); ?>

<div id="modSocialConnectMinimal" class="modSocialConnect<?php echo $moduleClassSuffix; ?>">

	<?php if($params->get('introductionMessage')):?>
	<div class="socialConnectIntroductionMessage"><?php echo $introductionMessage; ?></div>
	<?php endif; ?>

	<div class="socialConnectSignInBlock">
		<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post">
			<input class="socialConnectInput" placeholder="<?php echo $usernameLabel; ?>" type="text" name="username" />
			<a class="socialConnectLink" href="<?php echo $remindUsernameLink; ?>"><?php echo JText::_('JW_SC_FORGOT_YOUR_USERNAME'); ?></a>
			<input class="socialConnectInput modSocialConnectPassword" placeholder="<?php echo JText::_('JW_SC_PASSWORD') ?>" type="password" name="<?php echo $passwordFieldName; ?>" />
			<a class="socialConnectLink" href="<?php echo $resetPasswordLink; ?>"><?php echo JText::_('JW_SC_FORGOT_YOUR_PASSWORD'); ?></a>
			<div class="socialConnectClearFix">
				<button class="socialConnectButton socialConnectSignInButton socialConnectClearFix" type="submit">
					<i></i>
					<span><?php echo JText::_('JW_SC_SIGN_IN') ?></span>
				</button>
				<div class="socialConnectRememberBlock">
					<input id="modSocialConnectMinimalRemember" type="checkbox" name="remember" value="yes" />
					<label class="socialConnectLabel" for="modSocialConnectMinimalRemember"><?php echo JText::_('JW_SC_REMEMBER_ME') ?></label>
				</div>
			</div>
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" name="task" value="<?php echo $task; ?>" />
			<input type="hidden" name="return" value="<?php echo $returnURL; ?>" />
	  		<?php echo JHTML::_('form.token'); ?>
		</form>
	</div>

	<?php if($services): ?>
	<div class="socialConnectServicesBlock">
		<h4 class="socialConnectServicesMessage"><?php echo JText::_('JW_SC_OR_SIGN_IN_WITH'); ?></h4>
		<div class="socialConnectClearFix">
			<?php if($facebook): ?>
			<a title="::Facebook" class="socialConnectFacebookButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $facebookLink; ?>">
				<i></i>
				<span>Facebook</span>
			</a>
			<?php endif; ?>
			<?php if($twitter): ?>
			<a title="::Twitter" class="socialConnectTwitterButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $twitterLink; ?>">
				<i></i>
				<span>Twitter</span>
			</a>
			<?php endif; ?>
			<?php if($google): ?>
			<a title="::Google" class="socialConnectGoogleButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $googleLink; ?>">
				<i></i>
				<span>Google</span>
			</a>
			<?php endif; ?>
			<?php if($googlePlus): ?>
			<a title="::Google+" class="socialConnectGooglePlusButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $googlePlusLink; ?>">
				<i></i>
				<span>Google+</span>
			</a>
			<?php endif; ?>
			<?php if($linkedin): ?>
			<a title="::LinkedIn" class="socialConnectLinkedInButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $linkedinLink; ?>">
				<i></i>
				<span>LinkedIn</span>
			</a>
			<?php endif; ?>
			<?php if($github): ?>
			<a title="::GitHub" class="socialConnectGitHubButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $githubLink; ?>">
				<i></i>
				<span>GitHub</span>
			</a>
			<?php endif; ?>
			<?php if($wordpress): ?>
			<a title="::WordPress" class="socialConnectWordPressButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $wordpressLink; ?>">
				<i></i>
				<span>WordPress</span>
			</a>
			<?php endif; ?>
			<?php if($windows): ?>
			<a title="::Microsoft" class="socialConnectWindowsLiveButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $windowsLink; ?>">
				<i></i>
				<span>Microsoft</span>
			</a>
			<?php endif; ?>
			<?php if($instagram): ?>
			<a title="::Instagram" class="socialConnectInstagramButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $instagramLink; ?>">
				<i></i>
				<span>Instagram</span>
			</a>
			<?php endif; ?>
			<?php if($foursquare): ?>
			<a title="::Foursquare" class="socialConnectFoursquareButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $foursquareLink; ?>">
				<i></i>
				<span>Foursquare</span>
			</a>
			<?php endif; ?>
			<?php if($amazon): ?>
			<a title="::Amazon" class="socialConnectAmazonButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $amazonLink; ?>">
				<i></i>
				<span>Amazon</span>
			</a>
			<?php endif; ?>
			<?php if($disqus): ?>
			<a title="::DISQUS" class="socialConnectDisqusButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $disqusLink; ?>">
				<i></i>
				<span>DISQUS</span>
			</a>
			<?php endif; ?>
			<?php if($stackexchange): ?>
			<a title="::StackExchange" class="socialConnectStackExchangeButton socialConnectButton socialConnectServiceButton socialConnectClearFix hasTip" rel="nofollow" href="<?php echo $stackExchangeLink; ?>">
				<i></i>
				<span>StackExchange</span>
			</a>
			<?php endif; ?>
			<?php if($tumblr): ?>
			<a title="::Tumblr"  class="socialConnectTumblrButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $tumblrLink; ?>">
				<i></i>
				<span>Tumblr</span>
			</a>
			<?php endif; ?>
			<?php if($soundcloud): ?>
			<a title="::SoundCloud" class="socialConnectSoundCloudButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $soundcloudLink; ?>">
				<i></i>
				<span>SoundCloud</span>
			</a>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<span><?php echo JText::_('JW_SC_NOT_A_MEMBER_YET_SIGN_UP')?></span>
	<a class="socialConnectRegistrationButton" href="<?php echo $registrationLink; ?>"><?php echo JText::_('JW_SC_REGISTER'); ?></a>

	<?php if($params->get('footerMessage')):?>
	<div class="socialConnectFooterMessage"><?php echo $footerMessage; ?></div>
	<?php endif; ?>

</div>
