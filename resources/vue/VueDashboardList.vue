<template>
  <div>
    <template v-for="device in devices">

        <a class="selectable-link" :key="device.device_id" :href="goToUrl + (goToDevice ? device.device_id : device.device_site.ds_id)" draggable="false" @click.prevent>
        <div class="device-box selectable-content flex justify-between items-center" @click="goToDevice ? navigateToDevice(device) : navigateToSite(device.device_site)">
        <div class="ga" style="width: 96%;">
          <div class="flex items-center gs-2">

            <span class="icon-wrapper tt">
              <i @click.prevent.stop class="f7-icons icon icon-sm tts cursor-default" :style="device.alertIconStyle">{{ device.alertIcon }}</i>
              <span @click.prevent.stop class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">

                <!--DEVICE ALARM-->
                <div class="flex" v-if="device.isActiveAlarm">
                  <div class="text-left" style="width: 16rem;">
  <!--                <div>-->
                    {{ device.deviceType + ' ' + trans('Alarm') + ':' }}
                  </div>
                  <div class="text-left">
                    <div class="text-stroked text-bold" :style="{ color: colors.alarm }">{{ trans('Active') }}</div>
                  </div>
                </div>

                <!--DEVICE PERIODICAL TIMER-->
                <div class="flex">
                  <div class="text-left" style="width: 16rem;">
  <!--                <div>-->
                    {{ device.deviceType + ' ' + trans('Periodical Check') + ':' }}
                  </div>
                  <div class="text-left">
                    <div :style="{ color: String(device.expectedPeriodicalInHours).startsWith('-') ? colors.error : 'inherit' }" >
                      {{ device.expectedPeriodicalInHours }}
                    </div>
                  </div>
                </div>

                <!--DEVICE LOCAL CHECK TIMER-->
                <div class="flex">
                  <div class="text-left" style="width: 16rem;">
  <!--                <div>-->
                    {{ device.deviceType + ' ' + trans('Local Check') + ':' }}
                  </div>
                  <div class="text-left">
                    <div :style="{ color: String(device.expectedLocalCheckInHours).startsWith('-') ? colors.error : 'inherit' }">
                      {{ device.expectedLocalCheckInHours }}
                    </div>
                  </div>
                </div>

                <!--DEVICE NORMAL ALERTS-->
                <div class="flex" v-if="device.isMajorAlert || device.isMinorAlert">
                  <div class="text-left" style="width: 16rem;">
  <!--                <div>-->
                    {{ device.deviceType + ' ' + trans('Alerts') + ':' }}
                  </div>
                  <div class="text-left">
                    <div v-for="alert in device.majorAlerts" :style="{ color: colors.error }">{{ alert.at_desc }}</div>
                    <div v-for="alert in device.minorAlerts" :style="{ color: colors.warning }">{{ alert.at_desc }}</div>
                  </div>
                </div>

                <!--GATEWAY ALARM-->
                <div class="flex" v-if="device.isActiveAlarmGateway">
                  <div class="text-left" style="width: 16rem;">
  <!--                <div>-->
                    {{ trans('Gateway Alarm:') }}
                  </div>
                  <div class="text-left">
                    <div class="text-stroked text-bold" :style="{ color: colors.alarm }">{{ trans('Active') }}</div>
                  </div>
                </div>

                <!--GATEWAY PERIODICAL TIMER-->
                <div class="flex" v-if="device.device_site.gateway_type_device">
                  <div class="text-left" style="width: 16rem;">
  <!--                <div>-->
                    {{ trans('Gateway Periodical Check:') }}
                  </div>
                  <div class="text-left">
                    <div :style="{ color: String(device.expectedPeriodicalInHoursGateway).startsWith('-') ? colors.error : 'inherit' }">
                      {{ device.expectedPeriodicalInHoursGateway }}
                    </div>
                  </div>
                </div>

                <!--GATEWAY LOCAL CHECK TIMER-->
                <div class="flex" v-if="device.device_site.gateway_type_device">
                  <div class="text-left" style="width: 16rem;">
  <!--                <div>-->
                    {{ trans('Gateway Local Check:') }}
                  </div>
                  <div class="text-left">
                    <div :style="{ color: String(device.expectedLocalCheckInHoursGateway).startsWith('-') ? colors.error : 'inherit' }">
                      {{ device.expectedLocalCheckInHoursGateway }}
                    </div>
                  </div>
                </div>

                <!--GATEWAY NORMAL ALERTS-->
                <div class="flex" v-if="device.isMajorAlertGateway || device.isMinorAlertGateway">
                  <div class="text-left" style="width: 16rem;">
  <!--                <div>-->
                    {{ trans('Gateway Alerts:') }}
                  </div>
                  <div class="text-left">
                    <div v-for="alert in device.majorAlertsGateway" :style="{ color: colors.error }">{{ alert.at_desc }}</div>
                    <div v-for="alert in device.minorAlertsGateway" :style="{ color: colors.warning }">{{ alert.at_desc }}</div>
                  </div>
                </div>

              </span>
            </span>

            <a :href="(!isEmpty(device.gatewayLink) && !actionsForbidden) ? device.gatewayLink : '#'"
                @click.stop="(isEmpty(device.gatewayLink) || actionsForbidden) ? $event.preventDefault() : ''"
                :style="{ cursor: (isEmpty(device.gatewayLink) || actionsForbidden) ? 'default' : 'pointer' }">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon icon-sm tts" :style="device.connectivityIconStyle">{{ device.connectivityIcon }}</i>
                <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">

                  <div v-if="device.phoneNumber">
                    <div class="flex justify-between" v-for="(number, type) in device.numbers" v-if="number">
                      <div class="text-left" style="width: 12rem;">
                        {{ trans((type ?? '').toUpperCase())+':' }}
                      </div>
                      <div class="text-right">
                        {{ number }}
                      </div>
                    </div>
                  </div>
                  <div v-else>
                    {{ trans('No phone numbers or gateways are connected') }}
                  </div>

                  <div class="flex justify-between" v-if="device.gateway">
                    <div class="text-left" style="width: 12rem;">
                      {{ trans('Gateway expiration:') }}
                    </div>
                    <div :style="{ color: device.connectivityColor }">
                      {{ device.gatewayValidInHours }}
                    </div>
                  </div>

                </span>
              </span>
            </a>

            <span class="tt-vue">
              <span @click.prevent.stop :class="{ 'ttt-vue-on': tooltips['number_'+device.device_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.phoneNumber }}</span>
            </span>
            <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('number_'+device.device_id)" @mouseleave="hideTooltip('number_'+device.device_id)">{{ device.phoneNumber }}</span>

          </div>

          <div class="flex items-center gs-2">
            <span class="icon-wrapper tt">
              <i @click.prevent.stop @mouseenter="loadQrCodeForDevice(device)" class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(isEmpty(device.device_equipment))">tag</i>
              <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">
                <div>{{ trans('Equipment') }}</div>
                <div v-if="device.qrCodeValue" class="mt-2 pt-2 border-t border-gray-200">
                  <div class="flex flex-col items-center">
                    <div class="text-left mb-2">
                      {{ device.qrCodeFieldName }}: {{ device.qrCodeValue }}
                    </div>
                    <div v-if="device.qrCodeLoading" class="flex items-center justify-center" style="width: 120px; height: 120px;">
                      <div class="spinner-border" style="width: 2rem; height: 2rem;"></div>
                    </div>
                    <img v-else-if="device.qrCodeSvg" :src="device.qrCodeSvg" style="width: 120px; height: 120px;" />
                    <div v-else class="flex items-center justify-center text-gray-400" style="width: 120px; height: 120px;">
                      <span>Hover to load</span>
                    </div>
                  </div>
                </div>
              </span>
            </span>
            <span class="tt-vue">
              <span @click.prevent.stop :class="{ 'ttt-vue-on': tooltips['equipment_'+device.device_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.device_equipment }}</span>
            </span>
            <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('equipment_'+device.device_id)" @mouseleave="hideTooltip('equipment_'+device.device_id)">{{ device.device_equipment }}</span>
          </div>

          <div class="flex items-center gs-4">
            <span class="icon-wrapper tt">
              <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(isEmpty(device.address))">placemark</i>
              <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Address') }}</span>
            </span>
            <span class="tt-vue">
              <span @click.prevent.stop :class="{ 'ttt-vue-on': tooltips['address_'+device.device_id] }" class="ttt-vue ttt-vue-t bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.address }}</span>
            </span>
            <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('address_'+device.device_id)" @mouseleave="hideTooltip('address_'+device.device_id)">{{ device.address }}</span>
          </div>

          <!--DEVICE TYPE + PROTOCOL -->
          <div class="flex items-center gs-2">
            <span class="icon-wrapper tt">
              <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(isEmpty(device.deviceProtocol))">{{ device.deviceTypeIcon }}</i>
              <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.deviceType+' / '+device.deviceProtocol }}</span>
            </span>
            <span class="tt-vue">
              <span @click.prevent.stop :class="{ 'ttt-vue-on': tooltips['devicetype_'+device.device_id] }" class="ttt-vue ttt-vue-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.deviceType+' / '+device.deviceProtocol }}</span>
            </span>
            <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('devicetype_'+device.device_id)" @mouseleave="hideTooltip('devicetype_'+device.device_id)">{{ device.deviceProtocol }}</span>
          </div>

          <div class="flex items-center gs-2">
            <span class="icon-wrapper tt">
              <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(isEmpty(device.customFieldValue))">{{ device.customFieldIcon }}</i>
              <span @click.prevent.stop class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.customFieldName }}</span>
            </span>
            <span class="tt-vue">
              <span @click.prevent.stop :class="{ 'ttt-vue-on': tooltips['custom_'+device.device_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.customFieldValue }}</span>
            </span>
            <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('custom_'+device.device_id)" @mouseleave="hideTooltip('custom_'+device.device_id)">{{ device.customFieldValue }}</span>
          </div>

          <div class="flex items-center gs-3">
            <span class="icon-wrapper tt">
              <i @click.prevent.stop class="f7-icons icon default icon-sm tts cursor-default" :style="getIconStyle(isEmpty(device.comment))">bubble_left</i>
              <span @click.prevent.stop class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Last comment') }}</span>
            </span>
            <span class="tt-vue">
              <span @click.prevent.stop :class="{ 'ttt-vue-on': tooltips['comment_'+device.device_id] }" class="ttt-vue ttt-vue-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.comment }}</span>
            </span>
            <span @click.prevent.stop class="ml-4 elip cursor-default" @mouseenter="showTooltip('comment_'+device.device_id)" @mouseleave="hideTooltip('comment_'+device.device_id)">{{ device.comment }}</span>
          </div>

        </div>

        <div class="flex items-center mr-4">
          <a :href="!isEmpty(device.device_site?.ds_link) ? sanitizeLink(device.device_site?.ds_link) : '#'"
             @click.stop="isEmpty(device.device_site?.ds_link) ? $event.preventDefault() : ''"
             target="_blank"
             rel="noopener noreferrer"
             :style="{ cursor: isEmpty(device.device_site?.ds_link) ? 'default' : 'pointer' }">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts" :style="getIconStyle(isEmpty(device.device_site?.ds_link))">link</i>
              <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ device.device_site?.ds_link ?? trans('No link attached') }}</span>
            </span>
          </a>

  <!--        <a :href="goToUrl + (goToDevice ? device.device_id : device.device_site.ds_id)">-->
  <!--          <span class="icon-wrapper tt">-->
  <!--            <i class="f7-icons icon default icon-sm tts cursor-pointer" :style="getIconStyle(isEmpty(true))">arrow_up_right</i>-->
  <!--            <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"-->
  <!--                  style="width: max-content; zoom: 1.2;">{{ goToLabel }}</span>-->
  <!--          </span>-->
  <!--        </a>-->
        </div>

      </div>
      </a>
    </template>

    <div v-if="isEmpty(devices) && !loading" class="text-center mt-20">{{ emptyMessage }}</div>

    <div class="w-full text-center" style="margin-top: 5rem;">
      <pulse-loader :loading="loading" :color="colors.default" :size="'2rem'"></pulse-loader>
    </div>

  </div>
</template>

<script>
import axios from "axios";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue'
import { isEmpty, capitalizeFirstLowercaseRest } from "../assets/js/globalUtils"
import NotificationsMixin from "./mixins/NotificationsMixin";

export default {

  components: {
    PulseLoader,
  },

  mixins: [NotificationsMixin],

  props: {
    emptyMessage: {
      type: String,
      required: false,
      default: 'No results found' // might need translation
    },
    devicesUrl: {
      type: String,
      required: false,
      default: '/dashboard/devices'
    },
    actionsForbidden: {
      type: Boolean,
      required: false,
      default: false
    },
    goToUrl: {
      type: String,
      required: false,
      default: '/device-site/'
    },
    goToLabel: {
      type: String,
      required: false,
      default: 'Go to Site' // might need translation
    },
    goToDevice: {
      type: Boolean,
      required: false,
      default: false
    }
  },

  data() {
    return {
      devices: null,
      customFieldsConfig: null,
      allCustomFieldsConfig: null,
      translations: {},
      loading: false,
      currentFilters: null,
      cancelTokenSource: null,
      paginationData: {
        current: 1,
        total: 1
      },
      scrolledDownActive: false,
      alertPriority: [
        'active_alarm',
        'periodical_check_overdue',
        'local_check_overdue',
        'major_alerts',
        'minor_alerts',
      ],
      tooltips: {},
      colors: {
        alarm: 'yellow',
        error: 'lightcoral',
        warning: 'orange',
        success: 'lightgreen',
        default: 'lightblue',
        empty: 'lightgray',
      },
      initialFetchPromise: null,
      // --- debounce helpers for filtersChanged listener ---
      pendingFiltersTimeout: null,
      filtersBaseDelay: 700,
      filtersCurrentDelay: 700,
      filtersMaxDelay: 1500,
      lastFiltersHandledTime: 0,
      lastFiltersState: '',
    }
  },

  methods: {
    isEmpty(value) {
      return isEmpty(value)
    },

    trans(key) {
      return this.translations?.[key] || key;
    },

    async initialFetch() {
      try {
        let [customFieldsConfigRes, translationsRes] = await Promise.all([
          axios.get('/data/cfg'),
          axios.get('/data/translations'),
        ]);

        this.customFieldsConfig = customFieldsConfigRes.data.filter(config => config.dashboard)
        this.allCustomFieldsConfig = customFieldsConfigRes.data
        this.translations = translationsRes.data
      } catch (error) {
        console.error('Error fetching configs:', error);
        window.dispatchEvent(new CustomEvent('notifyerror', {detail: { message: this.trans('Error occurred on update') }}));
      }
    },

    loadDevices(filters = null) {
      // Cancel previous request if exists
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('New request initiated');
        this.loading = false
      }

      this.cancelTokenSource = axios.CancelToken.source();
      this.loading = true

      if (this.paginationData.current <= 1) {
        window.dispatchEvent(new CustomEvent('total_count_load'));
      }
      
      let url = this.devicesUrl + ('?page=' + this.paginationData.current);
      let config = {
        cancelToken: this.cancelTokenSource.token
      };

      if (filters) {
        config = {...config, method: 'post', url: url, data: { filters: filters }};
      } else {
        config = {...config, method: 'get', url: url};
      }
      
      axios.request(config)
        .then(response => {
          window.dispatchEvent(new CustomEvent('total_count_updated', { detail: { total: response.data.total }} ));

          let devices = response.data.data
          devices.forEach(device => {
            this.unpackExtraData(device)
          });

          if (this.paginationData.current > 1) {
            this.devices.push(...devices)
          } else {
            this.devices = devices
          }

          this.paginationData.total = response.data.last_page
          this.scrolledDownActive = true

          this.loading = false
        })
        .catch(error => {
          if (!axios.isCancel(error)) {
            console.dir(error)
            window.dispatchEvent(new CustomEvent('notifyerror', { detail: { message: this.trans('Error occurred on update') }}));
            this.loading = false
          }
        })
    },

    unpackExtraData(device) {
      this.unpackAlertsData(device)
      this.unpackGatewayAlertsData(device)
      this.unpackExpectedChecks(device)
      this.unpackConnectivityData(device)
      this.unpackCustomFieldsData(device)
      this.unpackOtherData(device)
      this.calculateAlertsIcon(device)
    },

    unpackAlertsData(device) {
      let alertsUnique = Array.isArray(device.device_alerts_unique)
        ? device.device_alerts_unique
        : Object.values(device.device_alerts_unique || {});

      device.alertTypes = alertsUnique.map(a => a.alert_type)
      device.isAlert = device.alertTypes.length > 0

      device.activeAlarms = device.alertTypes.filter(at => at.at_type === 'ALARM')
      device.isActiveAlarm = device.activeAlarms.length > 0

      device.periodicals = device.alertTypes.filter(at => at.at_type === 'PERIODICAL')
      device.isPeriodical = device.periodicals.length > 0

      device.majorAlerts = device.alertTypes.filter(at => at.alert_severity.as_type === 'MAJOR' && !['ALARM'].includes(at.at_type))
      device.isMajorAlert = device.majorAlerts.length > 0

      device.minorAlerts = device.alertTypes.filter(at => at.alert_severity.as_type === 'MINOR' && !['ALARM'].includes(at.at_type))
      device.isMinorAlert = device.minorAlerts.length > 0
    },

    unpackGatewayAlertsData(device) {
      let alertsUniqueGateway = Array.isArray(device.device_site.gateway_type_device?.device_alerts_unique)
        ? device.device_site.gateway_type_device?.device_alerts_unique
        : Object.values(device.device_site.gateway_type_device?.device_alerts_unique || {});

      device.alertTypesGateway = alertsUniqueGateway.map(a => a.alert_type)
      device.isAlertGateway = device.alertTypesGateway.length > 0

      device.activeAlarmsGateway = device.alertTypesGateway.filter(at => at.at_type === 'ALARM')
      device.isActiveAlarmGateway = device.activeAlarmsGateway.length > 0

      device.periodicalsGateway = device.alertTypesGateway.filter(at => at.at_type === 'PERIODICAL')
      device.isPeriodicalGateway = device.periodicalsGateway.length > 0

      device.majorAlertsGateway = device.alertTypesGateway.filter(at => at.alert_severity.as_type === 'MAJOR' && !['ALARM'].includes(at.at_type))
      device.isMajorAlertGateway = device.majorAlertsGateway.length > 0

      device.minorAlertsGateway = device.alertTypesGateway.filter(at => at.alert_severity.as_type === 'MINOR' && !['ALARM'].includes(at.at_type))
      device.isMinorAlertGateway = device.minorAlertsGateway.length > 0
    },

    unpackExpectedChecks(device) {
      if (device.expected_periodical_in_hours) {
        device.expectedPeriodicalInHours = -device.expected_periodical_in_hours + ' h'
        device.expectedPeriodicalState = Math.sign(device.expected_periodical_in_hours) < 0
        device.expectedPeriodicalState && (device.expectedPeriodicalInHours = '+ ' + device.expectedPeriodicalInHours)
      } else if (device.expected_periodical_in_hours === 0) {
        device.expectedPeriodicalInHours = -device.expected_periodical_in_minutes + ' min'
        device.expectedPeriodicalState = Math.sign(device.expected_periodical_in_minutes) < 0
        device.expectedPeriodicalState && (device.expectedPeriodicalInHours = '+ ' + device.expectedPeriodicalInHours)
      } else {
        device.expectedPeriodicalInHours = this.trans('Interval not defined')
        device.expectedPeriodicalState = null
      }

      if (!isEmpty(device.expected_local_check_in_hours)) {
        device.expectedLocalCheckInHours = -device.expected_local_check_in_hours + ' h'
        device.expectedLocalCheckState = Math.sign(device.expected_local_check_in_hours) < 0
        device.expectedLocalCheckState && (device.expectedLocalCheckInHours = '+ ' + device.expectedLocalCheckInHours)
      } else {
        device.expectedLocalCheckInHours = this.trans('Interval not defined')
        device.expectedLocalCheckState = null
      }

      if (isEmpty(device.device_site.gateway_type_device)) {
        return
      } else {
        var gatewayTypeDevice = device.device_site.gateway_type_device
      }

      if (!isEmpty(gatewayTypeDevice.expected_periodical_in_hours)) {
        device.expectedPeriodicalInHoursGateway = -gatewayTypeDevice.expected_periodical_in_hours + ' h'
        device.expectedPeriodicalStateGateway = Math.sign(gatewayTypeDevice.expected_periodical_in_hours) < 0
        device.expectedPeriodicalStateGateway && (device.expectedPeriodicalInHoursGateway = '+ ' + device.expectedPeriodicalInHoursGateway)
      } else {
        device.expectedPeriodicalInHoursGateway = this.trans('Interval not defined')
        device.expectedPeriodicalStateGateway = null
      }

      if (!isEmpty(gatewayTypeDevice.expected_local_check_in_hours)) {
        device.expectedLocalCheckInHoursGateway = -gatewayTypeDevice.expected_local_check_in_hours + ' h'
        device.expectedLocalCheckStateGateway = Math.sign(gatewayTypeDevice.expected_local_check_in_hours) < 0
        device.expectedLocalCheckStateGateway && (device.expectedLocalCheckInHoursGateway = '+ ' + device.expectedLocalCheckInHoursGateway)
      } else {
        device.expectedLocalCheckInHoursGateway = this.trans('Interval not defined')
        device.expectedLocalCheckStateGateway = null
      }
    },

    unpackConnectivityData(device) {
      device.gateway = device.device_site.gateway

      if (device.gateway?.valid_in_hours) {
        device.gatewayValidInHours = -device.gateway?.valid_in_hours + ' h'
        device.gatewayValidInHoursState = Math.sign(device.gateway?.valid_in_hours) < 0
        device.gatewayValidInHoursState && (device.gatewayValidInHours = '+ ' + device.gatewayValidInHours)
      } else if (device.gateway?.valid_in_hours === 0) {
        device.gatewayValidInHours = -device.gateway?.valid_in_minutes + ' min'
        device.gatewayValidInHoursState = device.gateway?.is_valid
        device.gatewayValidInHoursState && (device.gatewayValidInHours = '+ ' + device.gatewayValidInHours)
      } else {
        device.gatewayValidInHours = this.trans('Expiration not defined')
        device.gatewayValidInHoursState = null
      }

      device.connectivityIcon = device.gateway ? 'globe' : 'phone'
      device.connectivityColor = device.gateway ? (device.gatewayValidInHoursState === true ? this.colors.success : (device.gatewayValidInHoursState === false ? this.colors.error : this.colors.default)) : this.colors.default
      device.connectivityIconStyle = {'background-color': device.connectivityColor}

      device.mac = device.gateway?.dg_mac || ''
      device.imei = device.gateway?.dg_imei || ''

      // device.pstn = device.device_site?.pstn?.number_value ?? ''
      // device.sim = device.device_site?.sim?.number_value ?? ''
      // device.sip = device.device_site?.sip?.number_value ?? ''
      // device.pbx = device.device_site?.pbx?.number_value ?? ''

      device.pstn = device.device_site?.numbers?.find(number => number.number_type.nt_type === 'PSTN')?.number_value
      device.sim = device.device_site?.numbers?.find(number => number.number_type.nt_type === 'SIM')?.number_value
      device.sip = device.device_site?.numbers?.find(number => number.number_type.nt_type === 'SIP')?.number_value
      device.pbx = device.device_site?.numbers?.find(number => number.number_type.nt_type === 'PBX')?.number_value

      device.phoneNumber = device.sip || device.sim || device.pbx || device.pstn || device.mac || device.imei || ''
      device.numbers = {
        sip: device.sip,
        sim: device.sim,
        pbx: device.pbx,
        pstn: device.pstn,
        mac: device.mac,
        imei: device.imei,
      }

      device.gatewayLink = null
      if (device.gateway) {
        let link = '/settings/gateways?search='
        if (device.mac) {
          link = link + 'mac,' + device.mac
        } else if (device.imei) {
          link = link + 'imei,' + device.imei
        }
        device.gatewayLink = link
      }
    },

    unpackCustomFieldsData(device) {
      const dashboardConfig = this.customFieldsConfig?.find(config => true);
      device.customFieldIcon = dashboardConfig?.icon || 'info';
      device.customFieldName = dashboardConfig?.cfc_name || this.trans('Custom field');

      if (dashboardConfig?.cfc_is_device) {
        device.customFieldValue = device.custom_fields?.find(field =>
          field.cfv_cfc_id === dashboardConfig.cfc_id
        )?.cfv_value || '';
      } else {
        device.customFieldValue = device.device_site?.custom_fields?.find(field =>
          field.cfv_cfc_id === dashboardConfig?.cfc_id
        )?.cfv_value || '';
      }

      this.unpackQrCodeData(device);
    },

    unpackQrCodeData(device) {
      device.qrCodeValue = null;
      device.qrCodeFieldName = null;
      device.qrCodeSvg = null;
      device.qrCodeLoading = false;

      if (!device.custom_fields || !this.allCustomFieldsConfig || !this.allCustomFieldsConfig.length) {
        return;
      }

      const qrCodeConfig = this.allCustomFieldsConfig.find(config => 
        config.cfc_is_device && config.qr_code === true
      );

      if (!qrCodeConfig) {
        return;
      }

      const qrCodeField = device.custom_fields.find(field => 
        field.cfv_cfc_id === qrCodeConfig.cfc_id && field.cfv_value
      );

      if (qrCodeField && qrCodeField.cfv_value) {
        device.qrCodeValue = qrCodeField.cfv_value;
        device.qrCodeFieldName = qrCodeConfig.cfc_name;
        device.qrCodeSvg = null;
        device.qrCodeLoading = false;
      }
    },

    generateQrCode(value) {
      return `/data/qr-code?value=${encodeURIComponent(value)}`;
    },

    async loadQrCodeForDevice(device) {
      if (!device.qrCodeValue || device.qrCodeSvg || device.qrCodeLoading) {
        return;
      }

      device.qrCodeLoading = true;
      try {
        device.qrCodeSvg = this.generateQrCode(device.qrCodeValue);
      } catch (error) {
        console.error('Failed to load QR code:', error);
      } finally {
        device.qrCodeLoading = false;
      }
    },

    unpackOtherData(device) {
      device.comment = device.latest_comment?.dc_text || ''
      device.address = device.device_site?.address?.in_one_line || ''
      device.deviceProtocol = device.module?.module_desc || device.module?.module_name || ''
      device.deviceType = capitalizeFirstLowercaseRest(device.module?.module_type?.mt_type) || ''
      const deviceTypeIcons = {
        'Gateway': 'dot_radiowaves_right',
        'Telealarm': 'phone_arrow_up_right',
        'Intercom': 'speaker_2'
      }
      device.deviceTypeIcon = deviceTypeIcons[device.deviceType] || 'gear_alt';
    },

    calculateAlertsIcon(device) {
      device.alertIcon = (device.isActiveAlarm || device.isActiveAlarmGateway) ? 'bell' :
                         (device.isPeriodical || device.isPeriodicalGateway) ? 'arrow_clockwise' :
                         (device.isAlert || device.isAlertGateway) ? 'exclamationmark_triangle' :
                         'power';
      device.alertColor = (device.isActiveAlarm || device.isActiveAlarmGateway) ? this.colors.alarm :
                          (device.isPeriodical || device.isPeriodicalGateway) ? this.colors.error :
                          (device.isMajorAlert || device.isMajorAlertGateway) ? this.colors.error :
                          (device.isAlert || device.isAlertGateway) ? this.colors.warning :
                          this.colors.success;
      device.alertIconStyle = {'background-color': device.alertColor}
    },

    setNextPaginationPage() {
      if (this.paginationData.current < this.paginationData.total) {
        return ++this.paginationData.current
      }
      return false
    },

    // this method was for livewire - should be removed or merged
    // updatedFilters() {
    //   this.devices = null
    //   this.paginationData = {
    //     current: 1,
    //     total: 1,
    //   };
    //
    //   this.waitForConfigs().then(() => {
    //     this.loadDevices();
    //   });
    // },

    handleFiltersChanged(event) {
      const NOW = Date.now();
      const nextState = JSON.stringify({
        filters: event.detail.filters,
        searchTabs: event.detail.searchTabs,
      });

      if (nextState === this.lastFiltersState) {
        return;
      }

      const processEvent = () => {
        this.currentFilters = event.detail.filters;
        this.devices = null;
        this.paginationData = { current: 1, total: 1 };

        this.lastFiltersHandledTime = Date.now();
        this.lastFiltersState = nextState;

        this.waitForConfigs().then(() => {
          this.loadDevices(this.currentFilters);
        });

        this.filtersCurrentDelay = this.filtersBaseDelay;
      };

      const timeSinceLast = NOW - this.lastFiltersHandledTime;

      if (timeSinceLast > this.filtersCurrentDelay && !this.pendingFiltersTimeout) {
        processEvent();
      } else {
        if (this.pendingFiltersTimeout) {
          clearTimeout(this.pendingFiltersTimeout);
        }
        this.pendingFiltersTimeout = setTimeout(() => {
          processEvent();
          this.pendingFiltersTimeout = null;
        }, this.filtersCurrentDelay);

        // increase delay up to max to handle bursts
        this.filtersCurrentDelay = Math.min(Math.round(this.filtersCurrentDelay * 1.2), this.filtersMaxDelay);
      }
    },

    showTooltip(tooltip) {
      this.$set(this.tooltips, tooltip, true)
    },

    hideTooltip(tooltip) {
      this.$set(this.tooltips, tooltip, false)
    },

    capitalize(value) {
      return capitalizeFirstLowercaseRest(value)
    },

    getIconStyle(empty) {
      // return { 'background-color': !empty ? this.colors.default : this.colors.empty, 'color': !empty ? 'black' : 'white', 'opacity': !empty ? 1 : 0.5 }
      // return { 'background-color': !empty ? this.colors.default : this.colors.empty, 'opacity': !empty ? 1 : 0.6 }
      // return { 'background-color': this.colors.default, 'color': !empty ? 'black' : 'white', 'opacity': !empty ? 1 : 0.5 }
      return { 'background-color': this.colors.default, 'opacity': !empty ? 1 : 0.5 }
    },

    sanitizeLink(link) {
      link = link.replace(/(^\w+:|^)\/\//, '');
      return '//' + link;
    },

    isTextSelected() {
      const selection = window.getSelection();
      return selection && selection.toString().trim().length > 0;
    },

    navigateToSite(site) {
      if (this.isTextSelected()) {
        return;
      }
      window.location.href = this.goToUrl + site.ds_id;
    },

    navigateToDevice(device) {
      if (this.isTextSelected()) {
        return;
      }
      window.location.href = this.goToUrl + device.device_id;
    },


    async waitForConfigs() {
      if (this.initialFetchPromise) {
        return this.initialFetchPromise;
      }
      this.initialFetchPromise = this.initialFetch();
      return this.initialFetchPromise;
    },

  },

  created() {
    this.loading = true;
    // window.addEventListener('updatedFilters', this.updatedFilters); // livewire
    window.addEventListener('filtersChanged', this.handleFiltersChanged); // vue
    window.dispatchEvent(new CustomEvent('filtersPing'));
    console.log('LISTA WYSLALA PING at ' + Date.now())

    window.onscroll = (() => {
      if (this.scrolledDownActive && (window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 10)) {
        this.scrolledDownActive = false
        if (this.setNextPaginationPage()) {
          this.loadDevices(this.currentFilters)
        }
      }
    }).bind(this);

    this.waitForConfigs();
  },
  
  destroyed() {
    window.removeEventListener('updatedFilters', this.updatedFilters);
    window.removeEventListener('filtersChanged', this.handleFiltersChanged);
  }
}
</script>

<style lang="scss" scoped>

@import "resources/assets/sass/components-new/variables";
.device-box {
  width: 100%;
  height: 5rem;
  background-color: #fafafc;
  border: solid 1px #eaeaea;
  margin-block: 1rem;
  border-radius: 0.1rem;
}

.icon-wrapper {
  margin-left: 1rem;
}

.icon {
  border: solid 2px $darken4;
  border-radius: 0.2rem;
  width: 1.6rem;
  height: 1.6rem;
  display: flex;
  justify-content: center;
  align-items: center;

  &.default {
    background-color: lightblue;
  }
}

.icon-sm {
  font-size: 1.1rem;
}

.text-stroked {
  text-shadow: #000 0px 0px 1px,
  #000 0px 0px 1px,
  #000 0px 0px 1px,
  #000 0px 0px 1px,
  #000 0px 0px 1px,
  #000 0px 0px 1px;
}

.text-bold {
  font-weight: bold;
}

.selectable-link {
    //display: block;
    //text-decoration: none;
    //color: inherit;
    //cursor: text;

    cursor: pointer;
    -webkit-user-select: text;
    -webkit-select: text;
    -moz-user-select: text;
    -moz-select: text;
    -ms-user-select: text;
    -ms-select: text;
    user-select: text;
    select: text;
}

.selectable-link:hover {
    background-color: transparent;
}

.selectable-content {
    pointer-events: auto;
    user-select: text;
    -webkit-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
}

.selectable-content::selection {
    background-color: highlight;
    color: highlighttext;
}

.link-behavior {
    cursor: pointer;
}
</style>