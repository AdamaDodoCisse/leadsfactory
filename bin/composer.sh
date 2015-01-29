#!/bin/sh

rm -Rf web/bundles/tellawleadsfactory
rm -Rf vendor/tellaw/LeadsFactoryBundle/Tellaw/LeadsFactoryBundle

composer "$@"

rm -Rf web/bundles/tellawleadsfactory
ln -s ../../../leadsfactory/Resources/public web/bundles/tellawleadsfactory

mkdir -p vendor/tellaw/LeadsFactoryBundle/Tellaw
rm -Rf vendor/tellaw/LeadsFactoryBundle/Tellaw/LeadsFactoryBundle
ln -s ../../../../../leadsfactory vendor/tellaw/LeadsFactoryBundle/Tellaw/LeadsFactoryBundle
