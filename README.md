<div alt style="text-align: center; transform: scale(.5);">
	<picture>
		<source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/surlabs/STACK/ilias7_stack2021/templates/assets/stack-collage.png" />
		<img alt="STACK" src="https://raw.githubusercontent.com/surlabs/STACK/ilias7_stack2021/templates/assets/stack-collage.png" />
	</picture>
</div>

# STACK Question plugin for ILIAS

Welcome to the official repository for [STACK for ILIAS](https://stackforilias.com).

## What is STACK?

STACK is the world-leading open-source online assessment system for mathematics and STEM and the most powerful STEM learning tool available in ILIAS

> **Note** STACK for ILIAS is from 01.06.2023 officially maintained on the **SURLABS** repository. Original ILIFAU repository has been archived, please change your git remote to the new one in order to keep using the plugin.

## Installation & Update

### Software Requirements
STACK requires [PHP](https://php.net) version 7.4 to work properly on your ILIAS platform

STACK requires the [mbstring](https://www.php.net/manual/en/book.mbstring.php) PHP library to run properly, this library is by default included in ILIAS, but please ensure your installation includes that library on the composer.json dependencies.

STACK requires [Maxima](https://maxima.sourceforge.io/), an Open-Source Computer Algebra System to work. Ensure you have Maxima instance installed on the web server running your ILIAS installation, or you have a Maxima Pool / Goemaxima connection before installing this plugin in your platform.
* If you are using a local Maxima Installation, ensure it is at least Maxima **5.44**
* If you are using MaximaPool or GoeMaxima, the expected compiled version is **2023121100**

STACK requires [GNUPlot](https://www.gnuplot.info), which is used by Maxima to generate graphical plots of functions etc. It is freely available
and part of most Linux distrubutions. GNUplot needs to be installed on the web server
running your ILIAS and maxima installations.

STACK requires [Mathjax](https://www.mathjax.org), to be active on your ILIAS installation.

MathJax is an open source JavaScript display engine for mathematics. It is used by the STACK plugin
to display maths in question, user input validation and feedback. It can either be linked from
cdn.mathjax.org or downloaded to your own web server. It has to be configured in ILIAS:

1. Administration > Third Party Software > MathJax
2. Enable MathJax and enter the URL to MathJax (local or proposed cdn)
3. Save

### Installation steps
1. Create subdirectories, if necessary for Customizing/global/plugins/Modules/TestQuestionPool/Questions/
2. In Customizing/global/plugins/Modules/TestQuestionPool/Questions/ execute

```bash
git clone https://github.com/surlabs/STACK.git ./assStackQuestion
cd assStackQuestion
git checkout ilias7_stack_dev
```
3. STACK uses the ILIAS composer autoloader functionality so, after installing or update the plugin, ensure you run on the ILIAS root folder
```bash
composer du
php setup/setup.php update
```
***
**Please ensure you don't ignore plugins on composer.json**
***
4. Go to Administration > Plugins
5. Choose action "Install" or "Update" for the assStackQuestion plugin
6. Choose action "Activate" for the assStackQuestion plugin
7. Choose action "Refresh Languages" for the assStackQuestion plugin
8. Choose action "Configure" for the assStackQuestion plugin
9. Set the platform type and maxima servers according your installation
10. Go to the tab "Quality" and click "Do Health Check"

*If you can see the 3 Plots being rendered at the end of the screen, the plugin has been properly installed*

**Please, repeat this sequence everytime you update STACK**

# Authors
* This plugin includes the STACK core classes developed by Chris Sangwin, with support of Matti Harjula and Tim Hunt for its Moodle question type plugin version **4.5.0 (2023)**.
* This plugin was developed by Jesús Copado with Fred Neumann's support at the University of Erlangen between 2014 and 2022
* This plugin is currently maintained by Jesús Copado, Saúl Díaz and Daniel Cazalla through [SURLABS](https://surlabs.es)

# Bug Reports & Discussion
- Bug Reports: [Mantis](https://www.ilias.de/mantis) (Choose project "ILIAS plugins" and filter by category "STACK Question Type")
- SIG Mathe [Forum](https://docu.ilias.de/goto_docu_frm_7004.html)

## Known Issues
- Unit Tests are currently not available. Unit Tests created will remain in its current state until time for this at the moment non-extensive used feature has been fund and found.
- firstline syntax hint is not properly rendered in equivalence reasoning inputs.
- Check answer type is currently not evaluated
- Instant validation for textareas not working properly

# Version History
* The stable version 7.5.x for **ILIAS 7.13+** with the STACK Core version 4.5.0 can be found in the Github branch **ilias7_stack_dev**
* The stable version 7.0.x for **ILIAS 7.13+** with the STACK Core version 4.3.9 can be found in the Github branch **ilias7_stack**
* The stable version 3.5 for **ILIAS 7.13+** with the STACK Core version 4.2.2 can be found in the Github branch **unmaintained_ilias713_stack** is on only security issues mode.
* The stable version 3.4.3 for **ILIAS <= 7.12** with the STACK Core version 4.2.2 can be found in the Github branch **unmaintained_ilias7_stack** is on only security issues mode.
* The stable version 3.2.x for **ILIAS 6** is no longer maintained.
* The stable version 3.1.x for **ILIAS 5.4** is no longer maintained
* The stable version 3.0.x for **ILIAS 5.3** is no longer maintained
* The stable version 2.4.x for **ILIAS 5.2 to 5.3** is no longer maintained
* The stable version 2.3.x for **ILIAS 5.0 to 5.1** is no longer maintained

