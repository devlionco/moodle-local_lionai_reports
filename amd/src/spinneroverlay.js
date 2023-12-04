/* eslint-disable jsdoc/empty-tags */
/* eslint-disable require-jsdoc */
/* eslint-disable space-before-function-paren */
/* eslint-disable babel/object-curly-spacing */
/* eslint-disable jsdoc/require-jsdoc */
/* eslint-disable no-console */
/* eslint-disable no-unused-vars */
/* eslint-disable no-trailing-spaces */
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * spinneroverlay module for controlling spinneroverlay with spinner.
 *
 * @module     local_lionai_reports/spinneroverlay
 * @package    local_lionai_reports
 * @copyright  2023 Devlion.co <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Provides functions for managing a spinner overlay.
 * @namespace
 */
const spinneroverlay = {
  /**
   * Initializes the spinner overlay and adds it to the DOM.
   *
   * @param {string[]} controlledElements - An array of element IDs to be controlled by the overlay.
   */
  initspinneroverlay: (controlledElements) => {
    const spinneroverlay = document.createElement("div");
    spinneroverlay.innerHTML =
      '<img src="' +
      M.util.image_url("spinner", "local_lionai_reports") +
      '" alt="Loading..." class="spinner">';
    spinneroverlay.style.position = "fixed";
    spinneroverlay.style.top = "0";
    spinneroverlay.style.left = "0";
    spinneroverlay.style.width = "100%";
    spinneroverlay.style.height = "100%";
    spinneroverlay.style.backgroundColor = "rgba(0, 0, 0, 0.2)";
    spinneroverlay.style.display = "none";
    spinneroverlay.style.justifyContent = "center";
    spinneroverlay.style.alignItems = "center";
    spinneroverlay.style["z-index"] = 9999999;
    spinneroverlay.id = "spinneroverlay";
    document.body.appendChild(spinneroverlay);
  },

  /**
   * Hides the spinner overlay and re-enables controlled elements.
   *
   * @param {string[]} controlledElements - An array of element IDs to be re-enabled.
   */
  hidespinneroverlay: (controlledElements) => {
    const spinneroverlay = document.getElementById("spinneroverlay");
    spinneroverlay.style.display = "none";

    if (controlledElements && Array.isArray(controlledElements)) {
      controlledElements.forEach((elementId) => {
        const element = document.getElementById(elementId);
        if (element) {
          element.disabled = false;
        }
      });
    }
  },

  /**
   * Shows the spinner overlay and disables controlled elements.
   *
   * @param {string[]} controlledElements - An array of element IDs to be disabled.
   */
  showspinneroverlay: (controlledElements) => {
    const spinneroverlay = document.getElementById("spinneroverlay");
    spinneroverlay.style.display = "flex";

    if (controlledElements && Array.isArray(controlledElements)) {
      controlledElements.forEach((elementId) => {
        const element = document.getElementById(elementId);
        if (element) {
          element.disabled = true;
        }
      });
    }
  },
};

export default spinneroverlay;
