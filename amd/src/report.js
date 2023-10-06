/* eslint-disable capitalized-comments */
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
 * Connect smartreport functionality.
 *
 * @module      local_smartreport/report
 * @author      Anton P. <anton@devlion.co>
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from "core/ajax";
import $ from "jquery"; // TODO: Remove! Use only ES6
import Notification from "core/notification";
import { add } from "core/toast";
import Templates from "core/templates";
import spinneroverlay from "./spinneroverlay";
import "https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js";

let report;
let table;

const SELECTORS = {
  MAINELEMENT: "smartreport-area",
  REPORT: "smartreport-report",
  promptid: "id_userprompt",
  querysqlid: "id_querysql",
  senduserpromptid: "id_senduserprompt",
  submitbutton: "id_submitbutton",
  trytofixid: "id_trytofix",
  errordivid: "id_error_querysql",
  smartreporthistory: "smartreporthistory",
  queryresultwrapperid: "id_queryresultwrapper",
  queryresultid: "id_queryresult",
  queryresultmessage: "id_queryresultmessage",
};

const getReport = (id) =>
  Ajax.call([
    {
      methodname: "local_smartreport_getreport",
      args: {
        id: id,
      },
    },
  ])[0];

const renderReport = (_report) => {
  console.log(_report);
  _report.allreportsurl = M.cfg.wwwroot + "/local/smartreport";
  Templates.render("local_smartreport/report", _report)
    .then(function (html, js) {
      Templates.appendNodeContents(
        document.getElementById(SELECTORS.MAINELEMENT),
        html,
        js
      );

      const promptElem = document.getElementById(SELECTORS.promptid);
      const querysqlElem = document.getElementById(SELECTORS.querysqlid);
      const trytofixlElem = document.getElementById(SELECTORS.trytofixid);
      const queryresultmessage = document.getElementById(
        SELECTORS.queryresultmessage
      );
      const senduserpromptButton = document.getElementById(
        SELECTORS.senduserpromptid
      );
      const errorDiv = document.getElementById(SELECTORS.errordivid);
      const smartreporthistory = document.getElementById(
        SELECTORS.smartreporthistory
      );

      let cm;
      let reportid;

      const setSql = (sql) => {
        // TODO: after implementing CpdeMirror
        // let nextSibling = querysqlElem.nextElementSibling;
        // while (nextSibling) {
        //   if (nextSibling.classList.contains("CodeMirror")) {
        //     cm = nextSibling.CodeMirror;
        //     break;
        //   }
        //   nextSibling = nextSibling.nextElementSibling;
        // }
        // cm.setValue(sql);

        // Using textfield.
        querysqlElem.value = sql;

        getResult(sql);
      };

      const setUserPrompt = (value) => {
        promptElem.value = value;
      };

      let setTargetText = (newText, autoSave = false) => {
        setSql(newText);

        spinneroverlay.hidespinneroverlay([
          "id_userprompt",
          "id_querysql",
          "id_senduserprompt",
        ]);

        // Some delay to render sql with codemirror. Rework to callback.
        // setTimeout(function () {
        //   hidespinneroverlay();

        //   // TODO: autosave
        //   // if (autoSave) {
        //   //   virtualButtonClick();
        //   // }
        // }, 500);
      };

      let setqueryresultid = (data) => {
        console.log(data);
        const keys = Object.keys(data);

        if (table || (table && keys.length == 0)) {
          table.destroy();
          $("#id_queryresult").html("");
        }

        $("#" + SELECTORS.queryresultwrapperid).html("");
        const queryResultWrapper = $("#" + SELECTORS.queryresultwrapperid);
        const tableHtml = '<table class="display" id="id_queryresult"></table>';
        queryResultWrapper.html(tableHtml);

        if (keys.length > 0) {
          const firstItem = data[keys[0]];
          const columns = Object.keys(firstItem).map((key) => ({
            title: key,
            data: key,
          }));
          const dataArray = keys.map((key) => data[key]);
          table = $("#" + SELECTORS.queryresultid).DataTable({
            columns: columns,
            data: dataArray,
          });
        } else {
          // Handle the case where data is empty or has no keys.
          // setMessage("Data set is empty or has no keys.");
        }
      };

      const setMessage = (value) => {
        $("#" + SELECTORS.queryresultmessage).html(value);
      };

      let virtualButtonClick = () => {
        const button = document.getElementById("id_submitbutton");
        button.click();
      };

      let sendPrompt = (prompt) => {
        spinneroverlay.showspinneroverlay([
          "id_userprompt",
          "id_querysql",
          "id_senduserprompt",
        ]);

        console.log("prompt", prompt);
        console.log("report", report);

        Ajax.call([
          {
            methodname: "local_smartreport_send_prompt",
            args: {
              reportid: report.id,
              // model: model,
              prompt: prompt,
              engine: report.engine,
              conversationid: report.conversationid,
            },
            done: function (data) {
              let message = data.message;
              let correct = data.correct;
              console.log(data);
              let autoSave = true;
              setMessage("");
              if (!correct) {
                // add("Does not look like correct code. Use carefully.", {
                //   type: "danger",
                //   delay: 5000,
                // });
                setMessage("Does not look like correct code. Use carefully.");
                autoSave = false;
              }
              setTargetText(message, autoSave);
            },
            fail: function (error) {
              Notification.exception(error);
              spinneroverlay.hidespinneroverlay([
                "id_userprompt",
                "id_querysql",
                "id_senduserprompt",
              ]);
            },
          },
        ]);
      };

      let getResult = (query) => {
        // spinneroverlay.showspinneroverlay([
        //   "id_userprompt",
        //   "id_querysql",
        //   "id_senduserprompt",
        // ]);

        // console.log("prompt", prompt);
        // console.log("report", report);

        Ajax.call([
          {
            methodname: "local_smartreport_getresult",
            args: {
              query: query,
            },
            done: function (data) {
              console.log(data);
              let result = data.result;
              let message = data.message;
              data = JSON.parse(data.data);
              console.log(data);
              setMessage("");
              if (!result) {
                setMessage(message);
                // add("Does not look like correct code. Use carefully.", {
                // add(message, {
                //   type: "danger",
                //   delay: 5000,
                // });
                // return;
              }
              setqueryresultid(data);
            },
            fail: function (error) {
              Notification.exception(error);
            },
          },
        ]);
      };

      const showModal = () => {
        $("#historyModal").modal("hide");
      };

      const hideModal = () => {
        $("#historyModal").modal("hide");
      };

      const renderHistory = () => {
        // Get all the list items with the class 'smartreport-history-list-item'
        const historyItems = document.querySelectorAll(
          ".smartreport-history-list-item"
        );

        // Add a click event listener to each list item
        historyItems.forEach((item) => {
          item.addEventListener("click", (event) => {
            // Prevent the default behavior of the link (if it's an anchor tag)
            event.preventDefault();

            // Get the value and data-role attributes of the clicked item
            const value = item.textContent.trim();
            const role = item.getAttribute("data-role");

            // Use the value and role as needed
            console.log(`Value: ${value}, Role: ${role}`);

            hideModal();

            if (role == "user") {
              setUserPrompt(value);
              // sendPrompt(value);
            } else {
              setTargetText(value, true);
            }
          });
        });
      };

      senduserpromptButton.addEventListener("click", (event) => {
        console.log(promptElem.value);
        sendPrompt(promptElem.value);
      });

      if (trytofixlElem) {
        trytofixlElem.addEventListener("click", (event) => {
          console.log(promptElem.value);
          let value = "";
          // Get from BE.
          let trytofixprompt = "try to fix this sql:\n\n";
          value += trytofixprompt;
          let nextSibling = querysqlElem.nextElementSibling;
          while (nextSibling) {
            if (nextSibling.classList.contains("CodeMirror")) {
              cm = nextSibling.CodeMirror;
              break;
            }
            nextSibling = nextSibling.nextElementSibling;
          }
          let actualsql = cm.getValue();
          actualsql += "\n\n";
          value += actualsql;
          const codeElement = errorDiv.querySelector("code pre");
          if (codeElement) {
            const textContent = codeElement.textContent;
            console.log(textContent);
            value += textContent;
          }

          sendPrompt(value);
        });
      }

      promptElem.addEventListener("keydown", function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key === "Enter") {
          console.log(promptElem.value);
          sendPrompt(promptElem.value);
        }
      });

      // querysqlElem.addEventListener("keydown", function (event) {
      //   console.log(querysqlElem.value);
      //   getResult(querysqlElem.value);
      // });

      let timeoutId; // Variable to store the timeout ID for debouncing

      const handleQueryInputChange = (event) => {
        clearTimeout(timeoutId); // Clear the previous timeout (if any)

        // Get the input value
        const inputValue = querysqlElem.value;

        // Set a new timeout to delay the execution of getResult
        timeoutId = setTimeout(() => {
          getResult(inputValue);
        }, 500); // Adjust the debounce delay (in milliseconds) as needed
      };

      // Add a listener for the "keydown" event
      // querysqlElem.addEventListener("keyup", handleQueryInputChange);

      // Add a listener for the "paste" event
      // querysqlElem.addEventListener("paste", handleQueryInputChange);
      querysqlElem.addEventListener("input", handleQueryInputChange);

      // Examples.
      // Get all the dropdown items
      const dropdownItems = document.querySelectorAll(
        ".smartreport-examples-list .dropdown-item"
      );

      // Add a click event listener to each dropdown item
      dropdownItems.forEach((item) => {
        item.addEventListener("click", (event) => {
          // Prevent the default behavior of the link (preventing navigation)
          event.preventDefault();

          // Print the text content of the clicked dropdown item to the console
          let value = item.textContent.trim();
          console.log(value);
          setUserPrompt(value);
          sendPrompt(value);
        });
      });

      renderHistory();

      spinneroverlay.hidespinneroverlay();

      // TODO: remove
      // getResult("select * from {course}");

      // Show the modal
      // document.getElementById('historyModal').classList.add('show');
      // document.body.classList.add('modal-open');

      // // // Hide the modal
      // // document.getElementById('historyModal').classList.remove('show');
      // // document.body.classList.remove('modal-open');

      return;
    })
    .catch();
};

export const init = async (_reportid) => {
  console.log("init");
  console.log("reportid", _reportid);

  // Get report
  const response = await getReport(_reportid);
  report = JSON.parse(response.data).report;
  // this.setMessage("");
  renderReport(report);
};
