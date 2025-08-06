<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_lionai_reports
 * @category    string
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'דוחות LionAI';
$string['allreports'] = 'דוחות LionAI';
$string['list'] = 'רשימה';
$string['name'] = 'שם';
$string['id'] = 'מזהה';
$string['timecreated'] = 'נוסף';
$string['timemodified'] = 'עודכן';
$string['sendprompt'] = 'שלח בקשה';
$string['getresult'] = 'בצע קוד והצג תצוגה מקדימה';
$string['pickfromhistory'] = "בחר מהיסטוריית הדוח";
$string['examples'] = 'דוגמאות';
$string['example1'] = 'הצג את כל המשתמשים שהתחברו אתמול, הצג רק דוא"ל ושם מלא';
$string['example2'] = 'הצג את כל המבחנים הייחודיים בכל הקורסים, יש להציג שם מבחן, שם קורס ו-cmid';
$string['example3'] = 'הצג משתמשים ששמם הפרטי מכיל את האות A והדוא"ל שלהם מכיל את הספרה 2';
$string['example4'] = 'הצג את כל השאלות מסוג חיבור, שיש להן שם ייחודי';
$string['example5'] = 'קבל גרסה מההגדרות של התוסף mod_quiz';
$string['trytofix'] = 'נסה לתקן את השאילתה';
$string['trytofixtooltip'] = 'מנסה לתקן שגיאות בשאילתה באופן אוטומטי.
אם לא מתקבלות תוצאות, נסה לשנות את הנוסח.
מומלץ להשתמש בתבנית הדוגמה לשיפור הדיוק.';
$string['ctrlenter'] = 'ניתן להשתמש ב-CTRL+Enter';
$string['creator'] = 'יוצר';
$string['lastmessages'] = 'הודעות אחרונות';
$string['actions'] = 'פעולות';
$string['deletewarning'] = '<span class=\'text-warning\'>זהירות! מחיקה ללא אישור</span>';
$string['delete'] = 'מחק';
$string['exportcrautowarning'] = 'ייבוא אוטומטי ל-Configurable Reports';
$string['exportcrauto'] = 'הוסף ל-Configurable Reports';
$string['exportcrxmlwarning'] = 'ייצא קובץ XML לשימוש ב-Configurable';
$string['exportcrxml'] = 'ייצא קובץ XML';
$string['exportsqlwarning'] = 'ייצא כקובץ SQL (.sql)';
$string['exportsql'] = 'ייצא קובץ SQL';
$string['exportcsvwarning'] = 'ייצא תוצאות הדוח בפורמט CSV';
$string['exportcsv'] = 'ייצא קובץ CSV';
$string['exportexcelwarning'] = 'ייצא תוצאות הדוח בפורמט XLS';
$string['exportexcel'] = 'ייצא קובץ XLS';
$string['lionai_reports_apikey'] = 'מפתח API';
$string['lionai_reports_apikeyinfo'] = 'הגרסה החינמית מאפשרת 10 ביצועי בקשות בשבוע. לקבלת גרסת Pro, פנו אלינו בכתובת <a href="mailto:info@devlion.co">info@devlion.co</a>';
$string['lionai_reports_apiurl'] = 'כתובת API';
$string['lionai_reports_apiurlinfo'] = 'כתובת API';
$string['lionai_reports_allsee'] = 'דוחות גלויים לכולם';
$string['lionai_reports_allsee_info'] = "אפשר לכל יוצרי הדוחות לראות דוחות שנוצרו על ידי יוצרים אחרים באתר זה";
$string['lionai_reports_limitrecords'] = 'הגבלת רשומות';
$string['lionai_reports_limitrecordsinfo'] = 'בחר את מספר הרשומות שמוצגות בטבלת התצוגה המקדימה. (ברירת מחדל 10 רשומות, מקסימום 500 רשומות)';
$string['limited_to'] = 'מילת המפתח "LIMIT" אינה מותרת. מוגבל ל-{$a} רשומות';
$string['permission_require'] = "אין לך הרשאה, רק מנהלי האתר יכולים להשתמש בתוסף זה.";
$string['not_eligible_message'] = 'נראה שמפתח ה-API שלך שגוי או שמיצית את 10 הבקשות השבועיות בגרסה החינמית. לקבלת גרסת Pro, פנה אלינו בכתובת info@devlion.co';
$string['no_data_found'] = 'השאילתה לא החזירה תוצאות';
$string['thumbupbtn'] = 'אהבתי';
$string['thumbdownbtn'] = 'לא אהבתי';
$string['only_select'] = 'שגיאה - רק שאילתות "SELECT" מותרות!';
$string['privacy:metadata:local_lionai_reports'] = 'מידע על כל דוח, כולל המשתמש ששמר את הדוח והיסטוריית הבקשות והשאילתות.';
$string['privacy:metadata:local_lionai_reports:userid'] = 'מזהה המשתמש ששמר את הדוח.';
$string['privacy:metadata:local_lionai_reports:options'] = 'היסטוריית השאילתות והבקשות שהמשתמש השתמש בהן בדוח זה.';
$string['preview'] = 'תצוגה מקדימה';
$string['prompt'] = 'בקשה';
$string['sqlhistory'] = 'היסטוריית SQL';
$string['previewtotal'] = 'התקבלו בסך הכל {$a} תוצאות.';
$string['previewnote1'] = 'בטבלת התצוגה המקדימה ניתן לראות חלק מהן. לצפייה בכל התוצאות יש לייצא את הדוח.';
$string['previewnote2'] = 'מנהלים יכולים לשלוט בכמות הרשומות בטבלת התצוגה המקדימה דרך עמוד ההגדרות.';
$string['addprompt'] = 'הוסף בקשה: ';

