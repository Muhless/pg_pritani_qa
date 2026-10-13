import Alpine from "alpinejs";
import Swal from "sweetalert2";
import flatpickr from "flatpickr";
import { Indonesian } from "flatpickr/dist/l10n/id.js";
import "flatpickr/dist/flatpickr.min.css";

window.Alpine = Alpine;
window.Swal = Swal;
window.flatpickr = flatpickr;
flatpickr.localize(Indonesian);

Alpine.start();
