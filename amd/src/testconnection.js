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
 * Test connection button on the plugin settings page.
 *
 * @module     local_lionai_reports/testconnection
 * @copyright  2023 Devlion.co <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from "core/ajax";
import Notification from "core/notification";
import {get_string as getString} from "core/str";

/**
 * Gets the form field of a setting.
 *
 * @param {string} name - The setting name (component/setting).
 * @returns {HTMLInputElement|null}
 */
const getField = (name) => document.getElementById("id_s_" + name.replace("/", "_"));

/**
 * Gets the value of a form field.
 *
 * @param {string} name - The setting name.
 * @returns {string}
 */
const getValue = (name) => {
    const field = getField(name);
    return field ? field.value : "";
};

/**
 * Shows a status notification at the top of the page.
 *
 * @param {string} message - The message.
 * @param {string} type - success|error.
 */
const showStatus = (message, type) => {
    Notification.addNotification({message, type, closebutton: true});
};

/**
 * Initialise the button.
 *
 * @param {string} keyname - API key setting name.
 * @param {string} urlname - API URL setting name.
 */
export const init = (keyname, urlname) => {
    const button = document.getElementById("local_lionai_reports_testconnection");
    const result = document.getElementById("local_lionai_reports_testconnection_result");
    if (!button || !result) {
        return;
    }

    // Disabled placeholder button shown while the request is running.
    const waitbutton = document.createElement("button");
    waitbutton.type = "button";
    waitbutton.className = "btn btn-secondary d-none";
    waitbutton.disabled = true;
    button.insertAdjacentElement("afterend", waitbutton);
    getString("pleasewait", "local_lionai_reports").then((text) => {
        waitbutton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
            text;
        return text;
    }).catch(Notification.exception);

    button.addEventListener("click", async() => {
        button.classList.add("d-none");
        waitbutton.classList.remove("d-none");
        result.textContent = "";
        try {
            const response = await Ajax.call([{
                methodname: "local_lionai_reports_test_connection",
                args: {
                    apikey: getValue(keyname),
                    apiurl: getValue(urlname),
                },
            }])[0];
            result.className = "mt-2 " + (response.success ? "text-success" : "text-danger");
            result.textContent = response.message;
            showStatus(response.message, response.success ? "success" : "error");
        } catch (error) {
            result.className = "mt-2 text-danger";
            result.textContent = error && error.message ? error.message : String(error);
            showStatus(result.textContent, "error");
        } finally {
            waitbutton.classList.add("d-none");
            button.classList.remove("d-none");
        }
    });
};
