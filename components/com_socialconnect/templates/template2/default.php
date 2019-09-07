<?php
/**
 * @version     1.8.x
 * @package     SocialConnect
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license     http://www.joomlaworks.net/license
 */

defined('_JEXEC') or die; ?>

<div id="comSocialConnectContainer" class="socialConnectClearFix">

	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h1><?php echo $this->escape($this->params->get('page_title')); ?></h1>
	<?php endif; ?>

	<div class="socialConnectClearFix">

		<?php if($this->params->get('introductionMessage')):?>
		<div class="socialConnectIntroMessage"><?php echo $this->introductionMessage; ?></div>
		<?php endif; ?>

		<div class="socialConnectSignInBlock">
			<div class="socialConnectBlock">
				<h3 class="socialConnectSubHeading"><?php echo JText::_('JW_SC_ALREADY_A_MEMBER_SIGN_IN')?></h3>
				<h2 class="socialConnectHeading"><?php echo JText::_('JW_SC_SIGN_IN') ?></h2>
				<form action="<?php echo JRoute::_('index.php', true, $this->params->get('usesecure')); ?>" method="post">
					<?php if($this->params->get('signInMessage')):?>
					<div class="socialConnectSignInMessage"><?php echo $this->signInMessage; ?></div>
					<?php endif; ?>
					<div class="socialConnectClearFix">
						<div class="socialConnectColumn">
							<label class="socialConnectLabel" for="comSocialConnectUsername"><?php echo $this->usernameLabel; ?></label>
							<input id="comSocialConnectUsername" class="socialConnectInput" type="text" name="username" />
							<a class="socialConnectLink" href="<?php echo $this->remindUsernameLink; ?>"><?php echo JText::_('JW_SC_FORGOT_YOUR_USERNAME'); ?></a>
						</div>
						<div class="socialConnectColumn">
							<label class="socialConnectLabel" for="comSocialConnectPassword"><?php echo JText::_('JW_SC_PASSWORD') ?></label>
							<input id="comSocialConnectPassword" class="socialConnectInput" type="password" name="<?php echo $this->passwordFieldName; ?>" />
							<a class="socialConnectLink" href="<?php echo $this->resetPasswordLink; ?>"><?php echo JText::_('JW_SC_FORGOT_YOUR_PASSWORD'); ?></a>
						</div>
					</div>
					<button class="socialConnectButton socialConnectSignInButton socialConnectClearFix" type="submit">
						<i></i>
						<span><?php echo JText::_('JW_SC_SIGN_IN') ?></span>
					</button>
					<?php if($this->rememberMe): ?>
					<div class="socialConnectRememberBlock">
						<label class="socialConnectLabel" for="comSocialConnectRemember"><?php echo JText::_('JW_SC_REMEMBER_ME') ?></label>
						<input id="comSocialConnectRemember" type="checkbox" name="remember" value="yes" />
					</div>
					<?php endif; ?>
					<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
					<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
					<input type="hidden" name="return" value="<?php echo $this->returnURL; ?>" />
					<?php echo JHTML::_('form.token'); ?>
				</form>

				<?php if($this->services): ?>
				<div class="socialConnectServicesBlock">
					<h4 class="socialConnectServicesMessage"><?php echo JText::_('JW_SC_OR_SIGN_IN_WITH'); ?></h4>
					<div class="socialConnectClearFix">
						<?php if($this->facebook): ?>
						<a class="socialConnectFacebookButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->facebookLink; ?>">
							<i></i>
							<span>Facebook</span>
						</a>
						<?php endif; ?>
						<?php if($this->twitter): ?>
						<a class="socialConnectTwitterButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->twitterLink; ?>">
							<i></i>
							<span>Twitter</span>
						</a>
						<?php endif; ?>
						<?php if($this->google): ?>
						<a class="socialConnectGoogleButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->googleLink; ?>">
							<i></i>
							<span>Google</span>
						</a>
						<?php endif; ?>
						<?php if($this->googlePlus): ?>
						<a class="socialConnectGooglePlusButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->googlePlusLink; ?>">
							<i></i>
							<span>Google+</span>
						</a>
						<?php endif; ?>
						<?php if($this->linkedin): ?>
						<a class="socialConnectLinkedInButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->linkedinLink; ?>">
							<i></i>
							<span>LinkedIn</span>
						</a>
						<?php endif; ?>
						<?php if($this->github): ?>
						<a class="socialConnectGitHubButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->githubLink; ?>">
							<i></i>
							<span>GitHub</span>
						</a>
						<?php endif; ?>
						<?php if($this->wordpress): ?>
						<a class="socialConnectWordPressButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->wordpressLink; ?>">
							<i></i>
							<span>WordPress</span>
						</a>
						<?php endif; ?>
						<?php if($this->windows): ?>
						<a class="socialConnectWindowsLiveButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->windowsLink; ?>">
							<i></i>
							<span>Microsoft</span>
						</a>
						<?php endif; ?>
						<?php if($this->instagram): ?>
						<a class="socialConnectInstagramButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->instagramLink; ?>">
							<i></i>
							<span>Instagram</span>
						</a>
						<?php endif; ?>
						<?php if($this->foursquare): ?>
						<a class="socialConnectFoursquareButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->foursquareLink; ?>">
							<i></i>
							<span>Foursquare</span>
						</a>
						<?php endif; ?>
						<?php if($this->amazon): ?>
						<a class="socialConnectAmazonButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->amazonLink; ?>">
							<i></i>
							<span>Amazon</span>
						</a>
						<?php endif; ?>
						<?php if($this->disqus): ?>
						<a class="socialConnectDisqusButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->disqusLink; ?>">
							<i></i>
							<span>DISQUS</span>
						</a>
						<?php endif; ?>
						<?php if($this->stackexchange): ?>
						<a class="socialConnectStackExchangeButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->stackExchangeLink; ?>">
							<i></i>
							<span>StackExchange</span>
						</a>
						<?php endif; ?>
						<?php if($this->tumblr): ?>
						<a class="socialConnectTumblrButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->tumblrLink; ?>">
							<i></i>
							<span>Tumblr</span>
						</a>
						<?php endif; ?>
						<?php if($this->soundcloud): ?>
						<a class="socialConnectSoundCloudButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $this->soundcloudLink; ?>">
							<i></i>
							<span>SoundCloud</span>
						</a>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php if ($this->params->get('allowUserRegistration')) : ?>
	<div class="socialConnectRegistrationBlock">
		<div class="socialConnectBlock socialConnectClearFix">
			<div class="socialConnectRegistrationInnerBlock">
				<h3 class="socialConnectSubHeading"><?php echo JText::_('JW_SC_NOT_A_MEMBER_YET_SIGN_UP')?></h3>
				<h2 class="socialConnectHeading"><?php echo JText::_('JW_SC_REGISTER'); ?></h2>
				<a class="socialConnectButton socialConnectRegistrationButton socialConnectClearFix" href="<?php echo $this->registrationLink; ?>">
					<i></i>
					<span><?php echo JText::_('JW_SC_REGISTER'); ?></span>
				</a>
			</div>
			<?php if($this->params->get('registrationMessage')):?>
			<div class="socialConnectRegistrationMessage"><?php echo $this->registrationMessage; ?></div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

</div>
