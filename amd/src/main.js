/* eslint-disable no-debugger */
/* eslint-disable jsdoc/empty-tags */
/* eslint-disable no-console */
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
 * Javascript to initialise the main function.
 *
 * @module     local_lionai_reports/main
 * @package    local_lionai_reports
 * @copyright  2023 Devlion.co <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Module for managing the LionAI_Report UI, including rendering lists and reports.
 *
 * @module LionAI_ReportUI
 */
import Ajax from "core/ajax";
import Templates from "core/templates";
import * as LionAiReport from "./report";
import spinneroverlay from "./spinneroverlay";

/**
 * The CSS selectors used in the module.
 * @typedef {Object} Selectors
 * @property {string} MAINELEMENT - The main element selector.
 * @property {string} LIST - The list selector.
 * @property {string} REPORT - The report selector.
 */

/**
 * The main element selector and other CSS selectors.
 * @type {Selectors}
 */
var SELECTORS = {
  MAINELEMENT: "lionai_reports-area",
  LIST: "lionai_reports-list",
  REPORT: "lionai_reports-report",
};

/**
 * Retrieves a list of LionAI_Reports via an AJAX call.
 *
 * @returns {Promise} A promise that resolves with the list data.
 */
const getList = () =>
  Ajax.call([
    {
      methodname: "local_lionai_reports_getlist",
      args: {},
    },
  ])[0];

/**
 * Renders the list of LionAI_Reports.
 *
 * @param {string} listData - The list data to render.
 */
const renderList = (listData) => {
  listData = JSON.parse(listData);
  Templates.render("local_lionai_reports/list", listData)
    .then(function(html, js) {
      Templates.appendNodeContents(
        document.getElementById(SELECTORS.MAINELEMENT),
        html,
        js
      );
      spinneroverlay.hidespinneroverlay();

      return;
    })
    .catch();
};

/**
 * Initializes the LionAI_Report UI.
 *
 * @param {number} id - The ID of the report to initialize.
 */
export const init = async(id) => {

  spinneroverlay.initspinneroverlay();
  spinneroverlay.showspinneroverlay();

  if (id == 0) {
    const response = await getList();
    const listData = response.data;
    renderList(listData);
  }

  if (id != 0) {
    LionAiReport.init(id);
  }
};
