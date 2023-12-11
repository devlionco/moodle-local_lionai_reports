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
 * Connect lionai_reports functionality.
 *
 * @module      local_lionai_reports/report
 * @author      Anton P. <anton@devlion.co>
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from "core/ajax";
import Notification from "core/notification";
import Templates from "core/templates";
import spinneroverlay from "./spinneroverlay";
import DataTable from "./jquery.dataTables";
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import {get_string as getString} from 'core/str';
import * as CodeMirror from "./codemirror";

// eslint-disable-next-line no-unused-vars
// import "https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/sql/sql.min.js";

const dataTemp = {
    report: null,
    table: null,
    sqlChanged: false,
    sqlOriginal: '',
    editor: null,
};
const Selectors = {
    elements: {
        mainelement: "lionai_reports-area",
        report: "lionai_reports-report",
        promptTextarea: "id_userprompt",
        querysqlid: "id_querysql",
        senduserpromptid: "id_senduserprompt",
        getresultid: "id_getresult",
        submitbutton: "id_submitbutton",
        trytofixid: "id_trytofix",
        errordivid: "id_error_querysql",
        lionaiReportshistory: "lionai_reportshistory",
        queryresultwrapperid: "id_queryresultwrapper",
        queryresultid: "id_queryresult",
        queryresultmessage: "id_queryresultmessage",
        thmbup: "thmbup",
        thmbdown: "thmbdown",
        ratebtnswrapper: "ratebtns-wrapper"
    },
    targets: {}
};


/**
 * Retrieves a report by its ID using an AJAX call.
 *
 * @param {number} id - The unique identifier of the report to retrieve.
 * @returns {Promise<Object>} A Promise that resolves to the retrieved report object.
 * @throws {Error} If an error occurs during the AJAX call.
 */
const getReport = (id) =>
    Ajax.call([
        {
            methodname: "local_lionai_reports_getreport",
            args: {
                id: id,
            },
        },
    ])[0];

/**
 * Updates a report by its ID using an AJAX call.
 *
 * @param {number} id - The unique identifier of the report to update.
 * @param {string} [action="update"] - The action to perform on the report (default is "update").
 * @param {Object} data - The data to update the report with.
 * @returns {Promise<Object>} A Promise that resolves to the updated report object.
 * @throws {Error} If an error occurs during the AJAX call.
 */
const updateReport = (id, action = "update", data) => {
    return Ajax.call([
        {
            methodname: "local_lionai_reports_updatereport",
            args: {
                id: id,
                action: action,
                data: JSON.stringify(data),
            },
        },
    ])[0];
};

const initTargets = () => {
    Selectors.targets.promptElem = document.getElementById(Selectors.elements.promptTextarea);
    Selectors.targets.querysqlElem = document.getElementById(Selectors.elements.querysqlid);
    Selectors.targets.trytofixlElem = document.getElementById(Selectors.elements.trytofixid);
    Selectors.targets.queryresultmessage = document.getElementById(Selectors.elements.queryresultmessage);
    Selectors.targets.thmbup = document.getElementById(Selectors.elements.thmbup);
    Selectors.targets.thmbdown = document.getElementById(Selectors.elements.thmbdown);
    Selectors.targets.ratebtnswrapper = document.getElementById(Selectors.elements.ratebtnswrapper);

    Selectors.targets.senduserpromptButton = document.getElementById(Selectors.elements.senduserpromptid);
    Selectors.targets.getresultButton = document.getElementById(Selectors.elements.getresultid);
    Selectors.targets.errorDiv = document.getElementById(Selectors.elements.errordivid);
    Selectors.targets.lionaiReportshistory = document.getElementById(Selectors.elements.lionai_reportshistory);
};

/**
 * Renders a report by fetching its template, setting up event listeners, and updating the UI.
 *
 */
const renderReport = () => {
    Templates.render("local_lionai_reports/report", dataTemp.report)
        .then(function(html, js) {
            Templates.replaceNodeContents(
                document.getElementById(Selectors.elements.mainelement),
                html,
                js
            );

            initTargets();
            // TODO: Is it needed?
            // const changeActualSql = () => {
            //   dataTemp.actualsql = Selectors.targets.querysqlElem.value;
            //   if (dataTemp.sqlOriginal != actualsql) {
            //     originalDataChanged();
            //   }
            // }

            dataTemp.editor = CodeMirror.editorFromTextArea(Selectors.targets.querysqlElem);
            // TODO: Is it needed?
            // let virtualButtonClick = () => {
            //   const button = document.getElementById("id_submitbutton");
            //   button.click();
            // };
            Selectors.targets.thmbup.onclick = (e) =>
                ratePrompt(e.currentTarget.dataset.promptid, e.currentTarget.dataset.rate, e.currentTarget);

            Selectors.targets.thmbdown.onclick = (e) =>
                ratePrompt(e.currentTarget.dataset.promptid, e.currentTarget.dataset.rate, e.currentTarget);


            Selectors.targets.senduserpromptButton.addEventListener("click", () => {
                sendPrompt(Selectors.targets.promptElem.value);
            });

            Selectors.targets.getresultButton.addEventListener("click", () => {
                getResult(Selectors.targets.querysqlElem.value);
            });

            Selectors.targets.trytofixlElem.addEventListener("click", () => {
                let value = "";
                let trytofixprompt = "try to fix this sql:\n\n";
                value += trytofixprompt;

                let actualsql = Selectors.targets.querysqlElem.value;
                actualsql += "\n\n";
                value += actualsql;
                const textContent = Selectors.targets.queryresultmessage.textContent;
                value += textContent;

                sendPrompt(value);
            });


            Selectors.targets.querysqlElem.addEventListener("change", () => {
                let actualsql = Selectors.targets.querysqlElem.value;
                if (dataTemp.sqlOriginal != actualsql) {
                    originalDataChanged();
                }
            });

            Selectors.targets.promptElem.addEventListener("keydown", function(event) {
                if ((event.ctrlKey || event.metaKey) && event.key === "Enter") {
                    sendPrompt(Selectors.targets.promptElem.value);
                } else {
                    hideElement(Selectors.targets.ratebtnswrapper);
                }
            });

            // TODO: Is it needed?
            // let timeoutId;
            // Variable to store the timeout ID for debouncing

            // // TODO: Remove?
            // const handleQueryInputChange = () => {
            //   clearTimeout(timeoutId);
            //   const inputValue = Selectors.targets.querysqlElem.value;
            //   timeoutId = setTimeout(() => {
            //     getResult(inputValue);
            //   }, 500);
            // };

            const dropdownItems = document.querySelectorAll(
                ".lionai_reports-examples-list .dropdown-item"
            );

            dropdownItems.forEach((item) => {
                item.addEventListener("click", (event) => {
                    event.preventDefault();

                    let value = item.textContent.trim();
                    setUserPrompt(value);
                    sendPrompt(value);
                });
            });

            spinneroverlay.hidespinneroverlay();
            /**
             * Hides the "Try to Fix It" element by adding the "d-none" class.
             */

            hideElement(Selectors.targets.queryresultmessage);
            hideElement(Selectors.targets.trytofixlElem);
            hideElement(Selectors.targets.ratebtnswrapper);

            document
                .getElementById("edit-name-button")
                .addEventListener("click", function() {
                    document.getElementById("edit-name-button").classList.add("d-none");

                    var currentName = document.getElementById("edit-name").textContent;

                    var inputField = document.createElement("input");
                    inputField.type = "text";
                    inputField.value = currentName;
                    inputField.classList.add("w-100"); // Add the 'w-100' class

                    document.getElementById("edit-name").textContent = "";
                    document.getElementById("edit-name").appendChild(inputField);

                    // TODO: Discard by Focusout
                    // inputField.addEventListener("blur", async function (event) {
                    //   return;
                    // });

                    inputField.addEventListener("keydown", async function(event) {
                        // TODO: Discard by Escape
                        // if (event.key === "Escape") {
                        //   return;
                        // }
                        if (event.key === "Enter") {
                            var newName = inputField.value;
                            document.getElementById("edit-name").textContent = newName;
                            document
                                .getElementById("edit-name-button")
                                .classList.remove("d-none");
                            const updateResult = await updateReport(dataTemp.report.id, "update", {
                                name: newName,
                            });
                            if (updateResult) {
                                const response = await getReport(dataTemp.report.id);
                                dataTemp.report = JSON.parse(response.data).report;
                                renderReport();
                            }
                        }
                    });

                    inputField.focus();
                });
            document.getElementById('lionai_reportshistory').onclick = () => {
                showModal();
            };
            return;
        })
        .catch();
};


/**
 * Sets the query result data in a DataTable for display.
 *
 * @param {Object} data - The query result data.
 */
let setqueryresultid = (data) => {
    const keys = Object.keys(data);

    if (dataTemp.table || (dataTemp.table && keys.length == 0)) {
        dataTemp.table = '';
        document.getElementById(Selectors.elements.queryresultid).innerHTML = "";
    }

    document.getElementById(Selectors.elements.queryresultwrapperid).innerHTML = "";
    const queryResultWrapper = document.getElementById(Selectors.elements.queryresultwrapperid);
    const tableHtml = '<table class="display" id="id_queryresult"></table>';
    queryResultWrapper.innerHTML = tableHtml;

    if (keys.length > 0) {
        const firstItem = data[keys[0]];
        const columns = Object.keys(firstItem).map((key) => ({
            title: key,
            data: key,
        }));
        const dataArray = keys.map((key) => data[key]);

        dataTemp.table = new DataTable(`#${Selectors.elements.queryresultid}`, {
            columns: columns,
            data: dataArray,
            scrollX: true,
        });
    } else {
        // Handle the case where data is empty or has no keys.
        // setMessage("Data set is empty or has no keys.");
    }
};


// TODO: Is it needed?
// let cm;
// let reportid;

/**
 * Sets the SQL value in the query SQL element and triggers a result retrieval.
 *
 * @param {string} sql - The SQL string to set in the query SQL element.
 */
const setSql = (sql) => {

    Selectors.targets.querysqlElem.value = sql;
    let sqlEditors = Selectors.targets.querysqlElem.parentElement.querySelectorAll('.cm-editor');
    if (sqlEditors.length > 0) {
        sqlEditors.forEach((el) => {
            el.remove();
        });
    }

    dataTemp.editor = CodeMirror.editorFromTextArea(Selectors.targets.querysqlElem);
    getResult(sql);
};


/**
 * Shows the history modal by hiding it.
 */
const showModal = async() => {
    const modal = await ModalFactory.create({
        title: getString('pickfromhistory', 'local_lionai_reports'),
        body: Templates.render('local_lionai_reports/historymodal', dataTemp.report),
        footer: 'An example footer content',
    });
    modal.show();
    Selectors.targets.createdModal = modal;

    modal.getRoot().on(ModalEvents.bodyRendered, () => {
        renderHistory();
    });

};

/**
 * Hides the history modal.
 */
const hideModal = () => {
    Selectors.targets.createdModal.destroy();
};


const hideElement = (target) => {
    target.classList.add("d-none");
};

const showElement = (target) => {
    target.classList.remove("d-none");
};

/**
 * Retrieves query results using an AJAX call and updates the UI with the results.
 *
 * @param {string} query - The SQL query to execute and retrieve results for.
 */
const getResult = (query) => {
    /**
     * Hides the alert message element by adding the "d-none" class.
     */
    hideElement(Selectors.targets.queryresultmessage);
    hideElement(Selectors.targets.trytofixlElem);
    Ajax.call([
        {
            methodname: "local_lionai_reports_getresult",
            args: {
                query: query,
                id: dataTemp.report.id,
            },
            done: function(data) {
                let result = data.result;
                let message = data.message;
                data = JSON.parse(data.data);
                setMessage(message, !result);
                setqueryresultid(data);
            },
            fail: function(error) {
                Notification.exception(error);
            },
        },
    ]);
};

/**
 * Sets the target text, which includes SQL and query results, and optionally triggers auto-saving.
 *
 * @param {string} newText - The new text to set.
 */
const setTargetText = (newText/* , autoSave = false */) => {
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

/**
 * Sets a message to be displayed and optionally shows a correction prompt.
 *
 * @param {string} value - The message to display.
 * @param {number} [correct=2] - A flag to indicate if a correction prompt should be shown (default is 2).
 */
const setMessage = (value, correct = 2) => {
    if (value.length > 0) {
        document.getElementById(Selectors.elements.queryresultmessage).innerHTML = value;
        /**
         * Shows the alert message element by removing the "d-none" class.
         */
        showElement(Selectors.targets.queryresultmessage);
    }

    if (correct == 1) {
        /**
         * Shows the "Try to Fix It" element by removing the "d-none" class.
         */
        showElement(Selectors.targets.trytofixlElem);
    }
};


const originalDataChanged = () => {
    if (!dataTemp.sqlChanged) {
        dataTemp.sqlChanged = true;
        hideElement(Selectors.targets.ratebtnswrapper);
    }
};


/**
 * Sends a user prompt via an AJAX call and updates the target text based on the response.
 *
 * @param {string} prompt - The user prompt to send.
 */
const sendPrompt = (prompt) => {
    spinneroverlay.showspinneroverlay([
        "id_userprompt",
        "id_querysql",
        "id_senduserprompt",
    ]);

    hideElement(Selectors.targets.queryresultmessage);
    hideElement(Selectors.targets.trytofixlElem);
    hideElement(Selectors.targets.ratebtnswrapper);

    Ajax.call([
        {
            methodname: "local_lionai_reports_send_prompt",
            args: {
                reportid: dataTemp.report.id,
                prompt: prompt,
                engine: dataTemp.report.engine,
                conversationid: dataTemp.report.conversationid,
            },
            done: function(data) {
                let message = data.message;
                let correct = data.correct;
                let autoSave = true;
                if (correct != 2) {
                    setMessage(
                        "Does not look like correct code. Use carefully.",
                        correct
                    );
                    spinneroverlay.hidespinneroverlay([
                        "id_userprompt",
                        "id_querysql",
                        "id_senduserprompt",
                    ]);
                    Selectors.targets.querysqlElem.value = message;
                    let sqlEditors = Selectors.targets.querysqlElem.parentElement.querySelectorAll('.cm-editor');
                    if (sqlEditors.length > 0) {
                        sqlEditors.forEach((el) => {
                            el.remove();
                        });
                    }
                    dataTemp.editor = CodeMirror.editorFromTextArea(Selectors.targets.querysqlElem);
                    return;
                }
                dataTemp.sqlOriginal = message;
                dataTemp.sqlChanged = false;

                // Show rate btns.
                clearRateBtnsActiveClass();
                showElement(Selectors.targets.ratebtnswrapper);

                // Set promt id from responce to rate btns.
                setPromptidToBtns(data.promptid);

                setTargetText(message, autoSave);
            },
            fail: function(error) {
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

/**
 * Sets the user prompt value in the prompt element.
 *
 * @param {string} value - The user prompt value to set.
 */
const setUserPrompt = (value) => {
    Selectors.targets.promptElem.value = value;
};

/**
 * Renders history items and adds click event listeners to them.
 */
const renderHistory = () => {
    const historyItems = document.querySelectorAll(
        ".lionai_reports-history-list-item"
    );

    historyItems.forEach((item) => {
        item.addEventListener("click", (event) => {
            event.preventDefault();

            const value = item.textContent.trim();
            const role = item.getAttribute("data-role");

            hideModal();

            if (role == "user") {
                setUserPrompt(value);
                sendPrompt(value);
            } else {
                setTargetText(value, true);
            }
        });
    });
};

/**
 * Rate last sent prompt.
 *
 * @param {number} promptid - prompr id.
 * @param {number} rate - prompr rate.
 * @param {object} target - target element.
 */
const ratePrompt = (promptid, rate, target) => {
    rate = target.classList.contains('active') ? 0 : rate;
    Ajax.call([
        {
            methodname: "local_lionai_reports_rate_prompt",
            args: {
                promptid: +promptid,
                rate: +rate,
            },
            done: function() {
                clearRateBtnsActiveClass();
                return (rate === 0) ? target.classList.remove("active") : target.classList.add("active");
            },
            fail: function(error) {
                Notification.exception(error);
            },
        },
    ]);
};

const setPromptidToBtns = (promptid) => {
    Selectors.targets.thmbup.dataset.promptid = promptid;
    Selectors.targets.thmbdown.dataset.promptid = promptid;
};

const clearRateBtnsActiveClass = () => {
    Selectors.targets.thmbup.classList.remove("active");
    Selectors.targets.thmbdown.classList.remove("active");
};
/**
 * Initializes and renders a report with the given report ID.
 *
 * @param {number} _reportid - The unique identifier of the report to initialize and render.
 * @throws {Error} If an error occurs during report retrieval, parsing, or rendering.
 */
export const init = async(_reportid) => {

    const response = await getReport(_reportid);
    dataTemp.report = JSON.parse(response.data).report;

    renderReport();
};
