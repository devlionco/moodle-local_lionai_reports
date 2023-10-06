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
 * @module     local_smartreport/main
 * @package    local_smartreport
 * @copyright  2023 Devlion.co <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from "core/ajax";
import Templates from "core/templates";
import * as SmartReport from "./report";
import spinneroverlay from "./spinneroverlay";

var SELECTORS = {
  MAINELEMENT: "smartreport-area",
  LIST: "smartreport-list",
  REPORT: "smartreport-report",
};

const getList = () =>
  Ajax.call([
    {
      methodname: "local_smartreport_getlist",
      args: {},
    },
  ])[0];

const renderList = (listData) => {
  listData = JSON.parse(listData);
  Templates.render("local_smartreport/list", listData)
    .then(function (html, js) {
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

export const init = async (id) => {
  console.log("id", id);

  spinneroverlay.initspinneroverlay();
  spinneroverlay.showspinneroverlay();

  if (id == 0) {
    // Generate list
    const response = await getList();
    const listData = response.data;
    renderList(listData);
  }

  if (id != 0) {
    SmartReport.init(id);
  }
};
