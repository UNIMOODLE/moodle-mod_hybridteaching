# Moodle Hybrid Teaching Plugin

![Moodle Plugin](https://img.shields.io/badge/Moodle-Plugin-blue)
![License](https://img.shields.io/badge/License-GPLv3-blue.svg)


## Overview
The **Hybrid Teaching** plugin is designed to enhance the management of hybrid teaching sessions within the Moodle platform. It provides features for handling both in-person and remote classes, recording attendance, and facilitating access to session materials.

## Features
1. **Session Management**:
   - Easily manage physical and virtual classes from Moodle.
   - Record attendance for each session.
   - Provide access to session recordings and materials.

2. **System Independence**:
   - Compatible with many video conferencing system or video storage solution.
   - No additional configurations required.

3. **Grading**:
   - Assign grades based on attendance.
   - Configure customizable calculation methods.
   - Handle exceptions as needed.

4. **Open Source**:
   - Developed by the UNIMOODLE University Consortium.
   - Free and customizable.


## Usage
1. Create a new course or access an existing one.
2. Enable the **Hybrid Teaching** plugin.
3. Set up your sessions, manage attendance, and utilize grading functions.


## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/hybridteaching

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

# Credits and funding

HybridTeaching was designed by [UNIMOODLE Universities Group](https://unimoodle.github.io/) 

<img src="https://unimoodle.github.io/assets/images/allunimoodle-2383x376.png" height="120px" />

HybridTeaching was implemented by Moodle's Partner [ISYC](https://isyc.com/)

<img src="https://unimoodle.github.io/moodle-mod_hybridteaching/assets/images/logo-isyc-oncustomer-black-es-534x149.png" height="70px" />

This project was funded by the European Union Next Generation Program.

<img src="https://unimoodle.github.io/moodle-mod_hybridteaching/assets/images/unidigital-footer2024-1466x187.png" height="70px" />

## License ##

2023 UNIMOODLE University group https://unimoodle.github.io

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
