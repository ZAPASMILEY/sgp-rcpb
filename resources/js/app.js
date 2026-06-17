import '@fortawesome/fontawesome-free/css/all.min.css';

import TomSelect from 'tom-select';
window.TomSelect = TomSelect;

import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;

import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);
window.Chart = Chart;

import './bootstrap';
