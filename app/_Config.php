<?php

use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

SiteConfig::add_extension(CustomSiteConfig::class);
Member::add_extension(MemberExtension::class);
date_default_timezone_set('Asia/Jakarta');