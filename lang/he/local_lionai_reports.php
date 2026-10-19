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

$string['actions'] = 'פעולות';
$string['addnewreport'] = 'הוספת דוח חדש';
$string['addprompt'] = 'הוסף בקשה: ';
$string['aitokennotice'] = 'יש להגדיר בתוסף זה <strong>טוקן AI</strong> (מפתח API) כדי שיעבוד. לקבלת טוקן ולשימוש בגרסת הניסיון, כתבו ל-<a href="mailto:info@devlion.co">info@devlion.co</a>.';
$string['allreports'] = 'דוחות LionAI';
$string['creator'] = 'יוצר';
$string['ctrlenter'] = 'ניתן להשתמש ב-CTRL+Enter';
$string['curlerror'] = 'לא ניתן להתחבר לשירות ({$a}). אנא פנה אל info@devlion.co כדי לפתור את הבעיה.';
$string['delete'] = 'מחק';
$string['deleteconfirm'] = 'האם למחוק את הדוח "{$a}"? לא ניתן לבטל פעולה זו.';
$string['deletewarning'] = 'מחיקת דוח';
$string['editname'] = 'עריכת שם';
$string['example1'] = 'הצג את כל המשתמשים שהתחברו אתמול, הצג רק דוא"ל ושם מלא';
$string['example2'] = 'הצג את כל המבחנים הייחודיים בכל הקורסים, יש להציג שם מבחן, שם קורס ו-cmid';
$string['example3'] = 'הצג משתמשים ששמם הפרטי מכיל את האות A והדוא"ל שלהם מכיל את הספרה 2';
$string['example4'] = 'הצג את כל השאלות מסוג חיבור, שיש להן שם ייחודי';
$string['example5'] = 'קבל גרסה מההגדרות של התוסף mod_quiz';
$string['examples'] = 'דוגמאות';
$string['exportcrauto'] = 'הוסף ל-Configurable Reports';
$string['exportcrautowarning'] = 'ייבוא אוטומטי ל-Configurable Reports';
$string['exportcrxml'] = 'ייצא קובץ XML';
$string['exportcrxmlwarning'] = 'ייצא קובץ XML לשימוש ב-Configurable';
$string['exportcsv'] = 'ייצא קובץ CSV';
$string['exportcsvwarning'] = 'ייצא תוצאות הדוח בפורמט CSV';
$string['exportsql'] = 'ייצא קובץ SQL';
$string['exportsqlwarning'] = 'ייצא כקובץ SQL (.sql)';
$string['getresult'] = 'בצע קוד והצג תצוגה מקדימה';
$string['id'] = 'מזהה';
$string['invalidapikey'] = 'מפתח ה-API שלך אינו תקין, אנא פנה אל info@devlion.co כדי לפתור את הבעיה.';
$string['invalidapiresponse'] = 'התקבלה תגובה לא תקינה משירות LionAI. אנא פנה אל info@devlion.co כדי לפתור את הבעיה.';
$string['invalidapiurl'] = 'כתובת ה-API שלך אינה תקינה, אנא פנה אל info@devlion.co כדי לפתור את הבעיה.';
$string['lastmessages'] = 'הודעות אחרונות';
$string['limited_to'] = 'מילת המפתח "LIMIT" אינה מותרת. מוגבל ל-{$a} רשומות';
$string['lionai_reports:manage'] = 'ניהול LionAI Reports';
$string['lionai_reports_allsee'] = 'דוחות גלויים לכולם';
$string['lionai_reports_allsee_info'] = "אפשר לכל יוצרי הדוחות לראות דוחות שנוצרו על ידי יוצרים אחרים באתר זה";
$string['lionai_reports_apikey'] = 'מפתח API';
$string['lionai_reports_apikeyinfo'] = 'הגרסה החינמית מאפשרת 10 ביצועי בקשות בשבוע. לקבלת גרסת Pro, פנו אלינו בכתובת <a href="mailto:info@devlion.co">info@devlion.co</a>';
$string['lionai_reports_apiurl'] = 'כתובת API';
$string['lionai_reports_apiurlinfo'] = 'כתובת API';
$string['lionai_reports_exportlimit'] = 'מגבלת שורות לייצוא';
$string['lionai_reports_exportlimitinfo'] = 'מספר השורות המרבי בייצוא CSV. השורות נשלחות בזרימה, ולכן ערך גבוה אינו צורך הרבה זיכרון, אך ייצוא גדול מאוד עלול להיות איטי.';
$string['lionai_reports_limitrecords'] = 'הגבלת רשומות';
$string['lionai_reports_limitrecordsinfo'] = 'בחר את מספר הרשומות שמוצגות בטבלת התצוגה המקדימה. (ברירת מחדל 10 רשומות, מקסימום 500 רשומות)';
$string['list'] = 'רשימה';
$string['name'] = 'שם';
$string['newnameforreport'] = 'שם חדש לדוח {$a}';
$string['newnameforreportshort'] = 'שם חדש לדוח';
$string['no_data_found'] = 'השאילתה לא החזירה תוצאות';
$string['not_eligible_message'] = 'נראה שמפתח ה-API שלך שגוי או שמיצית את 10 הבקשות השבועיות בגרסה החינמית. לקבלת גרסת Pro, פנה אלינו בכתובת info@devlion.co';
$string['notcorrectcode'] = 'לא נראה כקוד תקין. השתמש בזהירות.';
$string['only_select'] = 'שגיאה - רק שאילתות "SELECT" מותרות!';
$string['permission_require'] = "אין לך הרשאה, רק מנהלי האתר יכולים להשתמש בתוסף זה.";
$string['pickfromhistory'] = "בחר מהיסטוריית הדוח";
$string['pleasewait'] = 'נא להמתין...';
$string['pluginname'] = 'דוחות LionAI';
$string['preview'] = 'תצוגה מקדימה';
$string['previewnote1'] = 'בטבלת התצוגה המקדימה ניתן לראות חלק מהן. לצפייה בכל התוצאות יש לייצא את הדוח.';
$string['previewnote2'] = 'מנהלים יכולים לשלוט בכמות הרשומות בטבלת התצוגה המקדימה דרך עמוד ההגדרות.';
$string['previewtotal'] = 'התקבלו בסך הכל {$a} תוצאות.';
$string['privacy:metadata:local_lionai_reports'] = 'מידע על כל דוח, כולל המשתמש ששמר את הדוח והיסטוריית הבקשות והשאילתות.';
$string['privacy:metadata:local_lionai_reports:options'] = 'היסטוריית השאילתות והבקשות שהמשתמש השתמש בהן בדוח זה.';
$string['privacy:metadata:local_lionai_reports:userid'] = 'מזהה המשתמש ששמר את הדוח.';
$string['prompt'] = 'בקשה';
$string['promptminwords'] = 'ההנחיה חייבת להכיל לפחות 2 מילים.';
$string['queryerror'] = 'לא ניתן היה להריץ את השאילתה.';
$string['reportnotfound'] = 'הדוח לא נמצא.';
$string['sendprompt'] = 'שלח בקשה';
$string['sqlhistory'] = 'היסטוריית SQL';
$string['testconnection'] = 'בדיקת חיבור';
$string['testconnection_badresponse'] = 'תגובה לא צפויה מהשירות: {$a}. אנא בדוק את כתובת ה-API או פנה אל info@devlion.co כדי לפתור את הבעיה.';
$string['testconnection_desc'] = 'בודק את מפתח ה-API וכתובת ה-URL שהוזנו למעלה (שמורים או לא). שים לב: נשלחת בקשת בדיקה אחת לשירות והיא עשויה להיחשב במכסת ההנחיות.';
$string['testconnection_httperror'] = 'השירות החזיר שגיאה (HTTP {$a->code}): {$a->detail}. אנא פנה אל info@devlion.co כדי לפתור את הבעיה.';
$string['testconnection_invalidurl'] = 'כתובת ה-API "{$a}" אינה תקינה (חייבת להתחיל ב-http:// או https://).';
$string['testconnection_nokey'] = 'יש להזין מפתח API.';
$string['testconnection_nourl'] = 'יש להזין כתובת API.';
$string['testconnection_ok'] = 'החיבור הצליח.';
$string['thumbdownbtn'] = 'לא אהבתי';
$string['thumbupbtn'] = 'אהבתי';
$string['timecreated'] = 'נוסף';
$string['timemodified'] = 'עודכן';
$string['trytofix'] = 'נסה לתקן את השאילתה';
$string['trytofixprompt'] = 'try to fix this sql:';
$string['trytofixtooltip'] = 'מנסה לתקן שגיאות בשאילתה באופן אוטומטי.
אם לא מתקבלות תוצאות, נסה לשנות את הנוסח.
מומלץ להשתמש בתבנית הדוגמה לשיפור הדיוק.';
