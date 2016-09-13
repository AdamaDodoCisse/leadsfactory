#!/bin/sh

rm -Rf web/bundles/tellawleadsfactory
rm -Rf vendor/tellaw/leadsfactory

composer "$@"

rm -Rf web/bundles/tellawleadsfactory
ln -s ../../../leadsfactory/Resources/public web/bundles/tellawleadsfactory

rm -Rf vendor/tellaw/leadsfactory/Tellaw/LeadsFactoryBundle
ln -s ../../../../../leadsfactory vendor/tellaw/leadsfactory/Tellaw/LeadsFactoryBundle
