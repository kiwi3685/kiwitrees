BUILD_DIR=build
BUILD_NUMBER=$(shell git log --oneline | wc -l)
BUILD_VERSION=$(if $(WT_RELEASE),$(BUILD_NUMBER),$(WT_VERSION)$(WT_RELEASE))
GIT_BRANCH=$(shell git symbolic-ref -q HEAD || git describe --tags --exact-match)
LANGUAGE_DIR=language
LANGUAGE_SRC=$(shell git grep -I --name-only --fixed-strings -e WT_I18N:: -- "*.php" "*.xml")
MO_FILES=$(patsubst %.po,%.mo,$(PO_FILES))
PO_FILES=$(wildcard $(LANGUAGE_DIR)/*.po $(LANGUAGE_DIR)/extra/*.po)
SHELL=bash
WT_VERSION=$(shell grep "'WT_VERSION'" includes/session.php | cut -d "'" -f 4 | awk -F - '{print $$1}')
WT_RELEASE=$(shell grep "'WT_VERSION'" includes/session.php | cut -d "'" -f 4 | awk -F - '{print $$2}')


################################################################################
# Gettext template (.POT) file
################################################################################
language/webtrees.pot: $(LANGUAGE_SRC)
	# Modify the .XML report files so that xgettext can scan them
	find modules*/ -name "*.xml" -exec cp -p {} {}.bak \;
	sed -i -e 's~\(WT_I18N::[^)]*[)]\)~<?php echo \1; ?>~g' modules*/*/*.xml
	echo $^ | xargs xgettext --package-name=webtrees --package-version=1.0 --msgid-bugs-address=i18n@webtrees.net --output=$@ --no-wrap --language=PHP --add-comments=I18N --from-code=utf-8 --keyword --keyword=translate:1 --keyword=translate_c:1c,2 --keyword=plural:1,2 --keyword=noop:1
	# Restore the .XML files
	find modules*/ -name "*.xml" -exec mv {}.bak {} \;

################################################################################
# Gettext catalog (.PO) files
################################################################################
$(PO_FILES): language/webtrees.pot
	msgmerge --no-wrap --sort-output --no-fuzzy-matching --output=$@ $@ $<

################################################################################
# Gettext translation (.MO) files
################################################################################
%.mo: %.po
	msgfmt --output=$@ $<
