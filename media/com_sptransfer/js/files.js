/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Browse to remote folder
 *
 * @package		SP Staging
 * 
 * @id_arr              id arrays of the stages
 * 
 */
function browse_remote(path) {
    document.getElementById('folder_remote').value = path;
    Joomla.submitform('files.browse');
}

function browse_local(path) {
    document.getElementById('folder_local').value = path;
    Joomla.submitform('files.browse');
}