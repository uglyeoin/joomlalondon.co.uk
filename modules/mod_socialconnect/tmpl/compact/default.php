<?php
/**
 * @version     1.8.x
 * @package     SocialConnect
 * @author      JoomlaWorks http://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license     http://www.joomlaworks.net/license
 */

defined('_JEXEC') or die; ?>

<div id="modSocialConnectCompact" class="socialConnectCompactLayout<?php if(!$services) { echo ' socialConnectNoServices';} ?> modSocialConnect<?php echo $moduleClassSuffix; ?>">

	<a class="socialConnectButton socialConnectToggler socialConnectClearFix">
		<i></i>
		<span><?php echo JText::_('JW_SC_SIGN_IN_OR_REGISTER'); ?></span>
	</a>

	<div class="socialConnectSignInBlockContainer socialConnectModal <?php echo $alignmentClass;?>">
		<div class="socialConnectClearFix">
			<div class="socialConnectSignInBlock">
				<div class="socialConnectInnerBlock">
					<h3 class="socialConnectSubHeading"><?php echo JText::_('JW_SC_ALREADY_A_MEMBER_SIGN_IN')?></h3>
					<h2 class="socialConnectHeading"><?php echo JText::_('JW_SC_SIGN_IN') ?></h2>
					<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post">
						<div class="socialConnectRow">
							<input placeholder="<?php echo $usernameLabel; ?>" class="socialConnectInput" type="text" name="username" />
							<div class="socialConnectClearFix">
								<a class="socialConnectLink" href="<?php echo $remindUsernameLink; ?>"><?php echo JText::_('JW_SC_FORGOT_YOUR_USERNAME'); ?></a>
							</div>
						</div>
						<div class="socialConnectRow">
							<input placeholder="<?php echo JText::_('JW_SC_PASSWORD') ?>" class="socialConnectInput modSocialConnectPassword" type="password" name="<?php echo $passwordFieldName; ?>" />
							<div class="socialConnectClearFix">
								<a class="socialConnectLink" href="<?php echo $resetPasswordLink; ?>"><?php echo JText::_('JW_SC_FORGOT_YOUR_PASSWORD'); ?></a>
							</div>
						</div>
						<button class="socialConnectButton socialConnectSignInButton socialConnectClearFix" type="submit">
							<i></i>
							<span><?php echo JText::_('JW_SC_SIGN_IN') ?></span>
						</button>
						<?php if($rememberMe): ?>
						<div class="socialConnectRememberBlock">
							<label class="socialConnectLabel" for="modSocialConnectCompactRemember"><?php echo JText::_('JW_SC_REMEMBER_ME') ?></label>
							<input id="modSocialConnectCompactRemember" type="checkbox" name="remember" value="yes" />
						</div>
						<?php endif; ?>
						<input type="hidden" name="option" value="<?php echo $option; ?>" />
						<input type="hidden" name="task" value="<?php echo $task; ?>" />
						<input type="hidden" name="return" value="<?php echo $returnURL; ?>" />
						<?php echo JHTML::_('form.token'); ?>
					</form>
				</div>
			</div>
			<?php if($services): ?>
			<div class="socialConnectServicesBlock">
				<div class="socialConnectInnerBlock">
					<h4 class="socialConnectServicesMessage"><?php echo JText::_('JW_SC_OR_SIGN_IN_WITH'); ?></h4>
					<div class="socialConnectClearFix">
						<?php if($facebook): ?>
						<a class="socialConnectFacebookButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $facebookLink; ?>">
							<i></i>
							<span>Facebook</span>
						</a>
						<?php endif; ?>
						<?php if($twitter): ?>
						<a class="socialConnectTwitterButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $twitterLink; ?>">
							<i></i>
							<span>Twitter</span>
						</a>
						<?php endif; ?>
						<?php if($google): ?>
						<a class="socialConnectGoogleButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $googleLink; ?>">
							<i></i>
							<span>Google</span>
						</a>
						<?php endif; ?>
						<?php if($googlePlus): ?>
						<a class="socialConnectGooglePlusButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $googlePlusLink; ?>">
							<i></i>
							<span>Google+</span>
						</a>
						<?php endif; ?>
						<?php if($linkedin): ?>
						<a class="socialConnectLinkedInButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $linkedinLink; ?>">
							<i></i>
							<span>LinkedIn</span>
						</a>
						<?php endif; ?>
						<?php if($github): ?>
						<a class="socialConnectGitHubButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $githubLink; ?>">
							<i></i>
							<span>GitHub</span>
						</a>
						<?php endif; ?>
						<?php if($wordpress): ?>
						<a class="socialConnectWordPressButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $wordpressLink; ?>">
							<i></i>
							<span>WordPress</span>
						</a>
						<?php endif; ?>
						<?php if($windows): ?>
						<a class="socialConnectWindowsLiveButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $windowsLink; ?>">
							<i></i>
							<span>Microsoft</span>
						</a>
						<?php endif; ?>
						<?php if($instagram): ?>
						<a class="socialConnectInstagramButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $instagramLink; ?>">
							<i></i>
							<span>Instagram</span>
						</a>
						<?php endif; ?>
						<?php if($foursquare): ?>
						<a class="socialConnectFoursquareButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $foursquareLink; ?>">
							<i></i>
							<span>Foursquare</span>
						</a>
						<?php endif; ?>
						<?php if($amazon): ?>
						<a class="socialConnectAmazonButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $amazonLink; ?>">
							<i></i>
							<span>Amazon</span>
						</a>
						<?php endif; ?>
						<?php if($disqus): ?>
						<a class="socialConnectDisqusButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $disqusLink; ?>">
							<i></i>
							<span>DISQUS</span>
						</a>
						<?php endif; ?>
						<?php if($stackexchange): ?>
						<a class="socialConnectStackExchangeButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $stackExchangeLink; ?>">
							<i></i>
							<span>StackExchange</span>
						</a>
						<?php endif; ?>
						<?php if($tumblr): ?>
						<a class="socialConnectTumblrButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $tumblrLink; ?>">
							<i></i>
							<span>Tumblr</span>
						</a>
						<?php endif; ?>
						<?php if($soundcloud): ?>
						<a class="socialConnectSoundCloudButton socialConnectButton socialConnectServiceButton socialConnectClearFix" rel="nofollow" href="<?php echo $soundcloudLink; ?>">
							<i></i>
							<span>SoundCloud</span>
						</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<?php if ($params->get('allowUserRegistration')) : ?>
		<div class="socialConnectRegistrationBlock socialConnectClearFix">
			<h3 class="socialConnectSubHeading"><?php echo JText::_('JW_SC_NOT_A_MEMBER_YET_SIGN_UP')?></h3>
			<a class="socialConnectButton socialConnectRegistrationButton socialConnectClearFix" href="<?php echo $registrationLink; ?>">
				<i></i>
				<span><?php echo JText::_('JW_SC_REGISTER'); ?></span>
			</a>
		</div>
		<?php endif; ?>
	</div>
</div>
