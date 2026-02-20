<template>
  <div>
    <div v-for="site in sites" :key="site.ds_id" class="site-box">

      <!--NORMAL SITE-->
      <div class="site-row" style="height: 5rem;" v-if="!site.edited">

        <!--1ST ROW-->
        <div class="gr-8" style="margin-bottom: 1.2rem">

          <!-- LEFT SITE ICONS -->
          <div class="gs-1 flex items-start">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': colors.success }">{{ 'power' }}</i>
              <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Enabled' }}</span>
            </span>

            <span class="icon-wrapper tt">
              <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.phoneNumber) ? colors.default : colors.empty }">{{ 'phone' }}</i>
              <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">

                <div v-if="site.phoneNumber">
                  <div class="flex" v-for="(number, type) in site.numbers" v-if="number">
                    <div class="text-left" style="width: 3rem;">
                      {{ capitalize(type)+':' }}
                    </div>
                    <div class="text-left">
                      {{ number }}
                    </div>
                  </div>
                </div>
                <div v-else>
                  {{ 'No phone numbers or gateways are connected' }}
                </div>

              </span>
            </span>

            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['number_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.phoneNumber }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('number_'+site.ds_id)" @mouseleave="hideTooltip('number_'+site.ds_id)">{{ site.phoneNumber }}</span>
          </div>

          <!--NORMAL SITE NAME-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.ds_name) ? colors.default : colors.empty }">building</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Site Name' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['sitename_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.ds_name }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('sitename_'+site.ds_id)" @mouseleave="hideTooltip('sitename_'+site.ds_id)">{{ site.ds_name }}</span>
          </div>

          <!--NORMAL ADDRESS-->
          <div class="gs-2 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.addressOneLine) ? colors.default : colors.empty }">placemark</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Address' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['address_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.addressOneLine }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('address_'+site.ds_id)" @mouseleave="hideTooltip('address_'+site.ds_id)">{{ site.addressOneLine }}</span>
          </div>

          <!--NORMAL PROTOCOL-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.siteProtocol) ? colors.default : colors.empty }">gear_alt</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Site Protocol' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['siteprotocol_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.siteProtocol }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('siteprotocol_'+site.ds_id)" @mouseleave="hideTooltip('siteprotocol_'+site.ds_id)">{{ site.siteProtocol }}</span>
          </div>

          <!--NORMAL ALARM1-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.alarmNumber) ? colors.default : colors.empty }">bell</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Alarm target 1' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['alarmnumber_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.alarmNumber }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('alarmnumber_'+site.ds_id)" @mouseleave="hideTooltip('alarmnumber_'+site.ds_id)">{{ site.alarmNumber }}</span>
          </div>

          <!--NORMAL PERIODICAL1-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.periodicalNumber) ? colors.default : colors.empty }">arrow_clockwise</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Periodical target 1' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['periodicalnumber_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.periodicalNumber }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('periodicalnumber_'+site.ds_id)" @mouseleave="hideTooltip('periodicalnumber_'+site.ds_id)">{{ site.periodicalNumber }}</span>
          </div>

          <!--1ST ROW SITE RIGHT ICONS-->
          <div class="gr-1 flex flex-col items-start gap-2 mr-4">
            <div class="gs-1 flex items-center justify-end">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   style="background-color: #8faadc;
                   color: white;">bubble_left
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.1;">{{ 'Show comments' }}</span>
              </span>

              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   style="background-color: #8faadc;
                   color: white;">calendar
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.1;">{{ 'Show history' }}</span>
              </span>

              <a :href="isEmpty(site.ds_link) ? 'javascript:void(0)' : '//'+site.ds_link"
                 target=”_blank”
                 :style="{ cursor: isEmpty(site.ds_link) ? 'default' : 'pointer', 'pointer-events': isEmpty(site.ds_link) ? 'none' : 'auto' }">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts"
                   :style="{'background-color': (isEmpty(site.ds_link) ? colors.empty : colors.button), 'color': 'white' }">link</i>
                <span v-if="!isEmpty(site.ds_link)"
                      class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.1;">{{ site.ds_link }}</span>
              </span>
              </a>

              <span class="icon-wrapper tt" @click="startEdit(site)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   style="background-color: #8faadc;
                   color: white;">square_pencil
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.1;">{{ 'Edit' }}</span>
              </span>
            </div>
          </div>
        </div>

        <!--2ND ROW-->
        <div class="gr-8">

          <!--2ND ROW LEFT SITE ICONS EMPTY -->
          <div class="gs-1 flex items-start"></div>

          <!--NORMAL SIP-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.sip) ? colors.default : colors.empty }">phone</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Sip' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['sip_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.sip }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('sip_'+site.ds_id)" @mouseleave="hideTooltip('sip_'+site.ds_id)">{{ site.sip }}</span>
          </div>

          <!--NORMAL SIM-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.sim) ? colors.default : colors.empty }">phone</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Sim' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['sim_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.sim }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('sim_'+site.ds_id)" @mouseleave="hideTooltip('sim_'+site.ds_id)">{{ site.sim }}</span>
          </div>

          <!--NORMAL PBX-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.pbx) ? colors.default : colors.empty }">phone</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Pbx' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['pbx_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.pbx }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('pbx_'+site.ds_id)" @mouseleave="hideTooltip('pbx_'+site.ds_id)">{{ site.pbx }}</span>
          </div>

          <!--NORMAL PSTN-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.pstn) ? colors.default : colors.empty }">phone</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Pstn' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['pstn_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.pstn }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('pstn_'+site.ds_id)" @mouseleave="hideTooltip('pstn_'+site.ds_id)">{{ site.pstn }}</span>
          </div>

          <!--NORMAL CUSTOM 1-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.customFields[0]?.value) ? colors.default : colors.empty }">{{ site.customFields[0]?.icon || 'info' }}</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.customFields[0]?.name || 'Custom field 1' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['custom1_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.customFields[0]?.value || '' }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('custom1_'+site.ds_id)" @mouseleave="hideTooltip('custom1_'+site.ds_id)">{{ site.customFields[0]?.value || '' }}</span>
          </div>

          <!--NORMAL CUSTOM2-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.customFields[1]?.value) ? colors.default : colors.empty }">{{ site.customFields[1]?.icon || 'info' }}</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.customFields[0]?.name || 'Custom field 2' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['custom2_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.customFields[1]?.value || '' }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('custom2_'+site.ds_id)" @mouseleave="hideTooltip('custom2_'+site.ds_id)">{{ site.customFields[1]?.value || '' }}</span>
          </div>

          <!--2ND ROW SITE RIGHT ICONS-->
          <div class="gs-1 gr-1 flex flex-col items-start gap-2 mr-4">
            <div class="gs-1 flex items-center justify-end">
              <a :href="'/device-site/'+site.ds_id" target=”_blank”>
                <span class="icon-wrapper tt">
                  <i class="f7-icons icon default icon-sm tts cursor-pointer"
                     style="background-color: #8faadc;
                     color: white;">arrow_up_right
                  </i>
                  <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                        style="width: max-content; zoom: 1.1;">{{ 'Go to Site' }}</span>
                </span>
              </a>
            </div>
          </div>
        </div>

      </div>


      <!--EDITED SITE-->
      <div class="site-row" style="height: 8rem;" v-if="site.edited">

        <!--EDITED 1ST ROW-->
        <div class="gr-8" style="margin-bottom: 1.2rem">

          <!--EDITED LEFT SITE ICONS -->
          <div class="gs-1 flex items-start">
            <!--EDITED SITE POWER-->
            <div class="gs-1 flex items-start">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': colors.success }">{{ 'power' }}</i>
                <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Site enabled' }}</span>
              </span>
            </div>
          </div>

          <!--EDITED SITE NAME-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.cloned.ds_name) ? colors.default : colors.empty }">building</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Site Name' }}</span>
            </span>
            <input class="input-edit ml-4 elip cursor-default" v-model="site.cloned.ds_name">
          </div>

          <!--EDITED ADDRESS-->
          <div class="gs-2 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.cloned.address) ? colors.default : colors.empty }">placemark</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Street / Zip / City / Country' }}</span>
            </span>
            <input class="input-edit ml-4 elip cursor-default" v-model="site.cloned.address.street">&nbsp;
            <input class="input-edit elip cursor-default" v-model="site.cloned.address.zip">&nbsp;
            <input class="input-edit elip cursor-default" v-model="site.cloned.address.city">&nbsp;
<!--            <input class="input-edit elip cursor-default" v-model="site.cloned.address.country">-->
            <select class="input-edit select-edit elip cursor-default" v-model="site.cloned.address.countryId">
              <option disabled value="">Please select one</option>
              <option v-for="(country, id) in countries" :value="id">{{ country }}</option>
            </select>
          </div>

          <!--EDITED PROTOCOL-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.siteProtocol) ? colors.default : colors.empty }">gear_alt</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Site Protocol' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['siteprotocol_'+site.ds_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.siteProtocol }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('siteprotocol_'+site.ds_id)" @mouseleave="hideTooltip('siteprotocol_'+site.ds_id)">{{ site.siteProtocol }}</span>
          </div>

          <!--EDITED ALARM1-->
          <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.cloned.alarmNumber) ? colors.default : colors.empty }">bell</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Alarm target 1' }}</span>
              </span>
              <input class="input-edit ml-4 elip cursor-default" v-model="site.cloned.alarmNumber">
          </div>

          <!--EDITED PERIODICAL1-->
          <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.cloned.periodicalNumber) ? colors.default : colors.empty }">arrow_clockwise</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Periodical target 1' }}</span>
              </span>
              <input class="input-edit ml-4 elip cursor-default" v-model="site.cloned.periodicalNumber">
          </div>

          <!--EDITED 1ST ROW SITE RIGHT ICONS-->
          <div class="gs-1 gr-1 flex flex-col items-start gap-2 mr-4">
            <div class="gs-1 flex items-center justify-end">
              <span class="icon-wrapper tt" @click="saveEdit(site)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   style="background-color: #8faadc;
                   color: green;">checkmark_alt
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.1;">{{ 'Save changes' }}</span>
              </span>

              <span class="icon-wrapper tt" @click="cancelEdit(site)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   style="background-color: #8faadc;
                   color: red;">xmark
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.1;">{{ 'Discard changes' }}</span>
              </span>
            </div>
          </div>
        </div>

        <!--EDITED 2ND ROW-->
        <div class="gr-8" v-if="site.edited" style="margin-bottom: 1.2rem">

          <!--EDITED 2ND ROW LEFT SITE ICONS EMPTY -->
          <div class="gs-1 flex items-start"></div>

          <!--EDITED SIP-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.cloned.sip) ? colors.default : colors.empty }">phone</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Sip' }}</span>
            </span>
            <input class="input-edit ml-4 elip cursor-default" v-model="site.cloned.sip">
          </div>

          <!--EDITED SIM-->
          <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.cloned.sim) ? colors.default : colors.empty }">phone</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Sim' }}</span>
              </span>
              <input class="input-edit ml-4 elip cursor-default" v-model="site.cloned.sim">
          </div>

          <!--EDITED PBX-->
          <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.cloned.pbx) ? colors.default : colors.empty }">phone</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Pbx' }}</span>
              </span>
              <input class="input-edit ml-4 elip cursor-default" v-model="site.cloned.pbx">
          </div>

          <!--EDITED PSTN-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.cloned.pstn) ? colors.default : colors.empty }">phone</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Pstn' }}</span>
            </span>
            <input class="input-edit ml-4 elip cursor-default" v-model="site.cloned.pstn">
          </div>

          <!--EDITED CUSTOM 1-->
          <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.clonedCustomFields[0].value) ? colors.default : colors.empty }">{{ site.clonedCustomFields[0].icon || 'info' }}</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.clonedCustomFields[0].name || 'Custom field 1' }}</span>
              </span>
              <input class="input-edit ml-4 elip cursor-default" v-model="site.clonedCustomFields[0].value">
          </div>

          <!--EDITED CUSTOM2-->
          <div class="gs-1 flex items-center">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.clonedCustomFields[1].value) ? colors.default : colors.empty }">{{ site.clonedCustomFields[1].icon || 'info' }}</i>
                <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ site.clonedCustomFields[1].name || 'Custom field 2' }}</span>
              </span>
              <input class="input-edit ml-4 elip cursor-default" v-model="site.clonedCustomFields[1].value">
          </div>

          <!--EDITED 2ND ROW SITE RIGHT ICONS-->
          <div class="gs-1 gr-1 flex flex-col items-start gap-2 mr-4">
            <div class="gs-1 flex items-center justify-end">
              <span class="icon-wrapper tt">
                  <i class="f7-icons icon default icon-sm tts cursor-pointer"
                     style="background-color: #8faadc;
                     color: white;">trash
                  </i>
                  <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Delete site' }}</span>
              </span>
            </div>
          </div>
        </div>

        <!--EDITED 3RD ROW-->
        <div class="gr-8" v-if="site.edited">

          <!--EDITED 3RD ROW LEFT SITE SPACE EMPTY -->
          <div class="gs-5 flex items-start"></div>

          <!--EDITED LINK-->
          <div class="gs-2 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(site.cloned.ds_link) ? colors.default : colors.empty }">link</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Link' }}</span>
            </span>
            <input class="input-edit ml-4 elip cursor-default" v-model="site.cloned.ds_link">
          </div>
        </div>

      </div>


      <!--Site devices list-->
      <div v-for="device in site.devices" :key="device.device_id">

        <div class="device-row-divider"></div>

        <div class="device-row gr-8">
          <!--NORMAL DEVICE LEFT ICONS-->
          <div class="gs-1 flex items-center" v-if="!device.edited">

            <!--power-->
            <span class="icon-wrapper tt">
              <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': device.device_enabled ? colors.success : colors.empty }">{{ 'power' }}</i>
              <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ device.device_enabled ? 'Enabled' : 'Disabled' }}</span>
            </span>

            <!--gateway-->
            <span class="icon-wrapper tt" v-if="device.gateway">
              <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': device.gatewayColor }">{{ 'globe' }}</i>
              <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">
                <div class="flex">
                  <div class="text-left" style="width: 9rem;">
                    {{ 'Gateway expiration:' }}
                  </div>
                  <div :style="{ color: device.gatewayColor }">
                    {{ device.gatewayValidInHours }}
                  </div>
                </div>
              </span>
            </span>

            <!--active alarm-->
            <span class="icon-wrapper tt" v-if="device.isActiveAlarm">
              <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': colors.alarm }">{{ 'bell' }}</i>
              <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Active Alarm' }}</span>
            </span>

            <!--periodical overdue-->
            <span class="icon-wrapper tt" v-if="device.isPeriodical">
              <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': colors.error }">{{ 'arrow_clockwise' }}</i>
              <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">
                <div class="flex">
                  <div class="text-left" style="width: 12rem;">
                    {{ device.deviceType + ' Periodical Check:' }}
                  </div>
                  <div class="text-left">
                    <div>{{ device.expectedPeriodicalInHours }}</div>
                  </div>
                </div>
              </span>
            </span>

            <!--local check overdue-->
            <span class="icon-wrapper tt" v-if="device.expectedLocalCheckState">
              <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': colors.warning }">{{ 'stopwatch' }}</i>
              <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">
                <div class="flex">
                  <div class="text-left" style="width: 12rem;">
                    {{ device.deviceType + ' Local Check:' }}
                  </div>
                  <div class="text-left">
                    <div>{{ device.expectedLocalCheckInHours }}</div>
                  </div>
                </div>
              </span>
            </span>

            <!--other alerts-->
            <span class="icon-wrapper tt" v-if="device.isMajorAlert || device.isMinorAlert">
              <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': device.isMajorAlert ? colors.error : colors.warning }">{{ 'exclamationmark_triangle' }}</i>
              <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">
                <div class="text-left" style="width: 12rem;">
                  {{ device.deviceType + ' Alerts:' }}
                </div>
                <div class="text-left">
                  <div v-for="alert in device.majorAlerts" :style="{ color: colors.error }">{{ alert.at_desc }}</div>
                  <div v-for="alert in device.minorAlerts" :style="{ color: colors.warning }">{{ alert.at_desc }}</div>
                </div>
              </span>
            </span>

            <!--device comments-->
            <span class="icon-wrapper tt" v-if="device.comment">
              <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': colors.default }">{{ 'bubble_left' }}</i>
              <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ device.comment }}</span>
            </span>
          </div>

          <!--EDITED DEVICE LEFT ICONS-->
          <div class="gs-1 flex items-center" v-if="device.edited">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon icon-sm tts cursor-default" :style="{ 'background-color': device.device_enabled ? colors.success : colors.empty }">{{ 'power' }}</i>
              <span class="ttt elip ttt-br bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ device.device_enabled ? 'Enabled' : 'Disabled' }}</span>
            </span>
          </div>

          <!--NORMAL EQUIPMENT-->
          <div class="gs-1 flex items-center" v-if="!device.edited">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">tag</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Equipment' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['equipment_'+device.device_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ device.device_equipment }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('equipment_'+device.device_id)" @mouseleave="hideTooltip('equipment_'+device.device_id)">{{ device.device_equipment }}</span>
          </div>

          <!--EDITED EQUIPMENT-->
          <div class="gs-1 flex items-center" v-if="device.edited">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">tag</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Equipment' }}</span>
            </span>
            <input class="input-edit ml-4 elip cursor-default" v-model="device.cloned.device_equipment">
          </div>

          <!--NORMAL IDENTITY/MODULE-->
          <div class="gs-1 flex items-center" v-if="!device.edited">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">gear_alt</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ getIdentityModuleLabel(device) }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['identitymodule_'+device.device_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ getIdentityModuleValue(device) }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('identitymodule_'+device.device_id)" @mouseleave="hideTooltip('identitymodule_'+device.device_id)">{{ getIdentityModuleValue(device) }}</span>
          </div>

          <!--EDITED IDENTITY/MODULE-->
          <div class="gs-1 flex items-center" v-if="device.edited">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">gear_alt</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Identity / Module' }}</span>
            </span>
            <input class="input-edit ml-4 elip cursor-default" v-model="device.cloned.device_identity">&nbsp;
            <input class="input-edit elip cursor-default" v-model="device.cloned.device_module">
          </div>

          <!--NORMAL PIN-->
          <div class="gs-1 flex items-center" v-if="!device.edited">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">lock</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Lock' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['pin_'+device.device_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ device.device_pin }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('pin_'+device.device_id)" @mouseleave="hideTooltip('pin_'+device.device_id)">{{ device.device_pin }}</span>
          </div>

          <!--EDITED PIN-->
          <div class="gs-1 flex items-center" v-if="device.edited">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">lock</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Lock' }}</span>
            </span>
            <input class="input-edit ml-4 elip cursor-default" v-model="device.cloned.device_pin">
          </div>

          <!--DEVICE TYPE-->
          <div class="gs-1 flex items-center">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" style="background-color: lightblue">gear_alt</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Device Type' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['devicetype_'+device.device_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ device.deviceType }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('devicetype_'+device.device_id)" @mouseleave="hideTooltip('devicetype_'+device.device_id)">{{ device.deviceType }}</span>
          </div>

          <!--NORMAL CUSTOM 1-->
          <div class="gs-1 flex items-center" v-if="!device.edited">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(device.customFields[0]?.value) ? colors.default : colors.empty }">{{ device.customFields[0]?.icon || 'info' }}</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ device.customFields[0]?.name || 'Custom field 1' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['custom1_'+device.device_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ device.customFields[0]?.value || '' }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('custom1_'+device.device_id)" @mouseleave="hideTooltip('custom1_'+device.device_id)">{{ device.customFields[0]?.value || '' }}</span>
          </div>

          <!--EDITED CUSTOM 1-->
          <div class="gs-1 flex items-center" v-if="device.edited">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(device.clonedCustomFields[0]?.value) ? colors.default : colors.empty }">{{ device.clonedCustomFields[0]?.icon || 'info' }}</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ device.clonedCustomFields[0]?.name || 'Custom field 1' }}</span>
            </span>
            <input class="input-edit ml-4 elip cursor-default" v-model="device.clonedCustomFields[0].value">
          </div>

          <!--NORMAL CUSTOM 2-->
          <div class="gs-1 flex items-center" v-if="!device.edited">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(device.customFields[1]?.value) ? colors.default : colors.empty }">{{ device.customFields[1]?.icon || 'info' }}</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ device.customFields[1]?.name || 'Custom field 1' }}</span>
            </span>
            <span class="tt-vue">
              <span :class="{ 'ttt-vue-on': tooltips['custom2_'+device.device_id] }" class="ttt-vue ttt-vue-trr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ device.customFields[1]?.value || '' }}</span>
            </span>
            <span class="ml-4 elip cursor-default" @mouseenter="showTooltip('custom2_'+device.device_id)" @mouseleave="hideTooltip('custom2_'+device.device_id)">{{ device.customFields[1]?.value || '' }}</span>
          </div>

          <!--EDITED CUSTOM 2-->
          <div class="gs-1 flex items-center" v-if="device.edited">
            <span class="icon-wrapper tt">
              <i class="f7-icons icon default icon-sm tts cursor-default" :style="{ 'background-color': !isEmpty(device.clonedCustomFields[1]?.value) ? colors.default : colors.empty }">{{ device.clonedCustomFields[1]?.icon || 'info' }}</i>
              <span class="ttt elip ttt-tr bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ device.clonedCustomFields[1]?.name || 'Custom field 1' }}</span>
            </span>
            <input class="input-edit ml-4 elip cursor-default" v-model="device.clonedCustomFields[1].value">
          </div>

          <!--DEVICE RIGHT ACTIONS-->
          <div class="gs-1 flex items-center justify-end mr-4">

            <div class="flex justify-end" v-if="!device.edited">
              <span class="icon-wrapper tt" v-if="device.actionButtons.trigger" @click="makeFsCall('trigger', device)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   :style="{'background-color': device.actionButtons.trigger === 'progress' ? 'greenyellow' : '#8faadc', 'color': device.actionButtons.trigger === 'progress' ? 'black' : 'white'}">bell_circle
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.1;">{{ 'Trigger' }}</span>
              </span>

              <span class="icon-wrapper tt" v-if="device.actionButtons.revival" @click="makeFsCall('revival', device)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   :style="{'background-color': device.actionButtons.revival === 'progress' ? 'greenyellow' : '#8faadc', 'color': device.actionButtons.revival === 'progress' ? 'black' : 'white'}">alarm
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.1;">{{ 'Revival' }}</span>
              </span>

              <span class="icon-wrapper tt" v-if="device.actionButtons.carcall" @click="makeFsCall('carcall', device)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   :style="{'background-color': device.actionButtons.carcall === 'progress' ? 'greenyellow' : '#8faadc', 'color': device.actionButtons.carcall === 'progress' ? 'black' : 'white'}">phone_circle
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.1;">{{ 'Carcall' }}</span>
              </span>

              <span class="icon-wrapper tt" v-if="device.actionButtons.set" @click="makeFsCall('set', device)">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   :style="{'background-color': device.actionButtons.set === 'progress' ? 'greenyellow' : '#8faadc', 'color': device.actionButtons.set === 'progress' ? 'black' : 'white'}">gear_alt
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm"
                      style="width: max-content; zoom: 1.1;">{{ 'Set' }}</span>
              </span>
            </div>

            <div class="flex justify-end" v-if="device.edited">
              <span class="icon-wrapper tt">
                <i class="f7-icons icon default icon-sm tts cursor-pointer"
                   style="background-color: #8faadc;
                   color: white;">trash
                </i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.1;">{{ 'Delete device' }}</span>
            </span>
            </div>

          </div>

        </div>
      </div>
    </div>

    <div class="w-full text-center" style="margin-top: 5rem;">
      <pulse-loader :loading="loading" :color="colors.default" :size="'2rem'"></pulse-loader>
    </div>

  </div>
</template>

<script>
import axios from "axios";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue'
import { isEmpty, capitalizeFirstLowercaseRest } from "../assets/js/globalUtils"

export default {

  components: {
    PulseLoader
  },

  data() {
    return {
      sites: null,
      customFieldsConfig: null,
      loading: false,
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
        button: '#8faadc'
      },
      accountId: null,
      settings: [],
      actionInProgress: false,
      countries: []
    }
  },

  methods: {
    isEmpty(value) {
      return isEmpty(value)
    },

    async initialFetch() {
      // window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}));
      this.loading = true
      try {
        let accountId = document.querySelector("meta[name='account-id']").getAttribute('content')
        if (!isEmpty(accountId)) {
          this.accountId = Number(accountId)
        } else {
          throw new Error('Account id is empty')
        }

        let [customFieldsConfigRes, sitesRes, settingsRes, countriesRes] = await Promise.all([
          axios.get('/equipment/customFieldsConfig'),
          axios.get('/equipment/sites' + ('?page=' + this.paginationData.current)),
          axios.get('/equipment/settings'),
          axios.get('/equipment/countries'),
        ]);

        this.customFieldsConfig = customFieldsConfigRes.data.filter(config => config.equipment)
        this.settings = settingsRes.data
        this.countries = countriesRes.data

        let sites = sitesRes.data.data;
        sites.forEach(site => {
          this.unpackExtraData(site)
        });
        this.sites = sites

        this.paginationData.total = sitesRes.data.last_page
        this.scrolledDownActive = true

        // window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }}));
        this.loading = false

      } catch (error) {
        console.error('Error fetching data:', error);
        // window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }}));
        window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: 'Error occurred on update'}}));
        this.loading = false
      }
    },

    loadSites() {
      // window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}));
      this.loading = true
      axios.get('/equipment/sites' + ('?page=' + this.paginationData.current))
        .then(response => {
          let sitesRes = response.data.data
          sitesRes.forEach(site => {
            this.unpackExtraData(site)
          });

          if (this.paginationData.current > 1) {
            this.sites.push(...sitesRes)
          } else {
            this.sites = sitesRes
          }

          this.paginationData.total = response.data.last_page
          this.scrolledDownActive = true

          // window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }}));
          this.loading = false
        })
        .catch(error => {
          console.dir(error)
          // window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }}));
          window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: 'Error occurred on update'}}));
          this.loading = false
        })
    },

    unpackExtraData(site) {
      this.unpackPhoneNumbers(site)
      this.unpackAddress(site)
      this.unpackProtocol(site)
      this.unpackSiteCustomFields(site)
      this.unpackSettingsData(site)
      this.unpackDeviceData(site)
      site.edited = false
    },

    unpackPhoneNumbers(site) {
      site.pstn = site.numbers?.find(number => number.number_type.nt_type === 'PSTN')?.number_value
      site.sim = site.numbers?.find(number => number.number_type.nt_type === 'SIM')?.number_value
      site.sip = site.numbers?.find(number => number.number_type.nt_type === 'SIP')?.number_value
      site.pbx = site.numbers?.find(number => number.number_type.nt_type === 'PBX')?.number_value

      site.phoneNumber = site.sip || site.sim || site.pbx || site.pstn || ''
      site.numbers = {
        sip: site.sip,
        sim: site.sim,
        pbx: site.pbx,
        pstn: site.pstn
      }
    },

    unpackAddress(site) {
      site.addressOneLine = site.address?.in_one_line || ''
    },

    unpackProtocol(site) {
      site.siteProtocol = site.module?.module_desc || site.module?.module_name || ''
    },

    unpackSiteCustomFields(site) {
      site.customFields = []
      this.customFieldsConfig.forEach(config => {
        if (config.is_device) return
        site.customFields.push({
          icon: config.icon ?? 'info',
          name: config.cfc_name ?? 'Custom field',
          value: site.custom_fields.find(custom => custom.cfv_cfc_id === config.cfc_id)?.cfv_value ?? '',
        })
      })
    },

    unpackSettingsData(site) {
      let alarmNumber = null
      let periodicalNumber = null
      site.devices.forEach(device => {
        alarmNumber = this.settings['device.alarm1.number']?.device_settings?.[device.device_id]
        periodicalNumber = this.settings['device.periodical1.number']?.device_settings?.[device.device_id]
      })
      site.alarmNumber = alarmNumber ?? this.settings['device.alarm1.number']?.ds_settings?.[site.ds_id] ??
                    this.settings['device.alarm1.number']?.acc_mod_settings?.[this.accountId]?.[site.ds_protocol_id] ??
                    this.settings['device.alarm1.number']?.mod_settings?.[site.ds_protocol_id] ??
                    this.settings['device.alarm1.number']?.settings ?? ''

      site.periodicalNumber = periodicalNumber ?? this.settings['device.periodical1.number']?.ds_settings?.[site.ds_id] ??
                    this.settings['device.periodical1.number']?.acc_mod_settings?.[this.accountId]?.[site.ds_protocol_id] ??
                    this.settings['device.periodical1.number']?.mod_settings?.[site.ds_protocol_id] ??
                    this.settings['device.periodical1.number']?.settings ?? ''
    },

    unpackDeviceData(site) {
      site.devices.forEach(device => {
        this.unpackAlertsData(device)
        this.unpackExpectedChecks(device)
        this.unpackGatewayData(device)
        this.unpackDeviceCustomFields(device)
        this.unpackOtherDeviceData(device)
        this.unpackActionButtons(device)
      })
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

      device.majorAlerts = device.alertTypes.filter(at => at.alert_severity.as_type === 'MAJOR' && !['ALARM','PERIODICAL'].includes(at.at_type))
      device.isMajorAlert = device.majorAlerts.length > 0

      device.minorAlerts = device.alertTypes.filter(at => at.alert_severity.as_type === 'MINOR' && !['ALARM','PERIODICAL'].includes(at.at_type))
      device.isMinorAlert = device.minorAlerts.length > 0
    },

    unpackExpectedChecks(device) {
      if (!isEmpty(device.expected_periodical_in_hours)) {
        device.expectedPeriodicalInHours = -device.expected_periodical_in_hours + ' h'
        device.expectedPeriodicalState = Math.sign(device.expected_periodical_in_hours) < 0
        device.expectedPeriodicalState && (device.expectedPeriodicalInHours = '+ ' + device.expectedPeriodicalInHours)
      } else {
        device.expectedPeriodicalInHours = 'Interval not defined'
        device.expectedPeriodicalState = null
      }

      if (!isEmpty(device.expected_local_check_in_hours)) {
        device.expectedLocalCheckInHours = -device.expected_local_check_in_hours + ' h'
        device.expectedLocalCheckState = Math.sign(device.expected_local_check_in_hours) < 0
        device.expectedLocalCheckState && (device.expectedLocalCheckInHours = '+ ' + device.expectedLocalCheckInHours)
      } else {
        device.expectedLocalCheckInHours = 'Interval not defined'
        device.expectedLocalCheckState = null
      }
    },

    unpackGatewayData(device) {
      if (isEmpty(device.gateway)) {
        return
      }

      if (device.gateway?.valid_in_hours) {
        device.gatewayValidInHours = -device.gateway?.valid_in_hours + ' h'
        device.gatewayValidInHoursState = Math.sign(device.gateway?.valid_in_hours) < 0
        device.gatewayValidInHoursState && (device.gatewayValidInHours = '+ ' + device.gatewayValidInHours)
      } else if (device.gateway?.valid_in_hours === 0) {
        device.gatewayValidInHours = device.gateway?.valid_in_hours + ' h'
        device.gatewayValidInHoursState = device.gateway?.is_valid
        let sign = device.gatewayValidInHoursState ? '+ ' : '- '
        device.gatewayValidInHours = sign + device.gatewayValidInHours
      } else {
        device.gatewayValidInHours = 'Expiration not defined'
        device.gatewayValidInHoursState = null
      }

      device.gatewayColor = device.gatewayValidInHoursState === true ? this.colors.success : (device.gatewayValidInHoursState === false ? this.colors.error : this.colors.default)

      device.mac = device.gateway?.dg_mac || ''
      device.imei = device.gateway?.dg_imei || ''

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

    unpackDeviceCustomFields(device) {
      device.customFields = []
      this.customFieldsConfig.forEach(config => {
        if (!config.is_device) return
        device.customFields.push({
          icon: config.icon ?? 'info',
          name: config.cfc_name ?? 'Custom field',
          value: device.custom_fields.find(custom => custom.cfv_cfc_id === config.cfc_id)?.cfv_value ?? '',
        })
      })
    },

    unpackOtherDeviceData(device) {
      device.comment = device.latest_comment?.dc_text || ''
      device.deviceProtocol = device.module?.module_desc || device.module?.module_name || ''
      device.deviceType = capitalizeFirstLowercaseRest(device.module?.module_type?.mt_type) || ''
    },

    unpackActionButtons(device) {
      device.actionButtons = {
        carcall: device.module?.funktions?.some(obj => obj.function_call === '_carcall'),
        revival: device.module?.funktions?.some(obj => obj.function_call === '_revival'),
        set: device.module?.funktions?.some(obj => obj.function_call === '_set'),
        trigger: device.module?.funktions?.some(obj => obj.function_call === '_trigger')
      }
    },

    getIdentityModuleLabel(device) {
      if (!isEmpty(device.device_identity) && isEmpty(device.device_module)) {
        return 'Identity'
      } else if (isEmpty(device.device_identity) && !isEmpty(device.device_module)) {
        return 'Module'
      } else {
        return 'Identity/Module'
      }
    },

    getIdentityModuleValue(device) {
      if (!isEmpty(device.device_identity) && isEmpty(device.device_module)) {
        return device.device_identity
      } else if (isEmpty(device.device_identity) && !isEmpty(device.device_module)) {
        return device.device_module
      } else if (!isEmpty(device.device_identity) && !isEmpty(device.device_module)) {
        return device.device_identity + '/' + device.device_module
      } else {
        return ''
      }
    },

    setNextPaginationPage() {
      if (this.paginationData.current < this.paginationData.total) {
        return ++this.paginationData.current
      }
      return false
    },

    updatedFilters() {
      this.sites = null
      this.paginationData = {
        current: 1,
        total: 1
      }
      this.loadSites()
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

    startEdit(site) {
      site.cloned = { ...site }
      site.clonedCustomFields = [];
      [0, 1].forEach(index => {
        site.clonedCustomFields.push({
          icon: site.customFields[index]?.icon ?? 'info',
          name: site.customFields[index]?.name ?? 'Custom field',
          value: site.customFields[index]?.value ?? '',
        })
      })

      site.cloned.address = {
        street: site.address?.address_value ?? '',
        zip: site.address?.location?.location_postcode ?? '',
        city: site.address?.location?.location_value ?? '',
        countryId: site.address?.location?.country?.country_id ?? ''
      }

      site.devices.forEach(device => {
        device.cloned = { ...device }
        device.clonedCustomFields = [];
        [0, 1].forEach(index => {
          device.clonedCustomFields.push({
            icon: device.customFields[index]?.icon ?? 'info',
            name: device.customFields[index]?.name ?? 'Custom field',
            value: device.customFields[index]?.value ?? '',
          })
        })
        device.edited = true
      })

      site.edited = true
    },

    cancelEdit(site) {
      site.devices.forEach(device => {
        device.edited = false
      })
      site.edited = false
    },

    saveEdit(site) {
      site.devices.forEach(device => {
        device.edited = false
      })
      site.edited = false
    },

    makeFsCall(action, device) {
      this.actionInProgress = true
      device.actionButtons[action] = 'progress'

      window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}));
      axios.post('equipment/fsCall', { action: action, deviceId: device.device_id })
        .then(response => {
          if (response.data === 'success') {
            window.dispatchEvent(new CustomEvent('notify', {detail: [this.capitalize(action)+' action succeeded', 'success'] } ));
          } else {
            window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.capitalize(action)+' action failed'}} ));
          }
        })
        .catch(error => {
          window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.capitalize(action)+' action failed'}} ));
        })
        .finally(() => {
          window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }} ));
          this.actionInProgress = false
          device.actionButtons[action] = true
      })
    },
  },

  created() {
    window.addEventListener('updatedFilters', this.updatedFilters);

    window.onscroll = (() => {
      if (this.scrolledDownActive && (window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 10)) {
        this.scrolledDownActive = false
        if (this.setNextPaginationPage()) {
          this.loadSites()
        }
      }
    }).bind(this);

    this.initialFetch()
  }
}
</script>

<style lang="scss" scoped>

@import "resources/assets/sass/components-new/variables";

.site-box {
  width: 100%;
  background-color: #fafafc;
  border: solid 1px #eaeaea;
  margin-block: 3.2rem;
  border-radius: 0.1rem;
  padding-top: 1.2rem;
}

.site-row {
  margin-bottom: 0.5rem;
}

.device-row {
  height: 4.5rem;
}

.device-row-divider {
  border-top: #eaeaea solid 1px;
  margin: auto;
  width: 98%;
}

.icon-wrapper {
  margin-left: 1rem;
}

.icon {
  padding: 0.1rem;
  border: solid 2px $darken4;
  border-radius: 0.2rem;
  width: 1.5rem;
  height: 1.5rem;

  &.default {
    background-color: lightblue;
  }
}

.icon-sm {
  font-size: 1rem;
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

.input-edit {
  border-style: solid;
  border-width: 1px;
  border-color: gray;
  width: 100%;
  outline:none;
  padding-left: 0.3rem;
  border-radius: 0.13rem;
}

.select-edit {
  font-size: 0.8rem;
  line-height: 1.7;
  padding: 0;
  //width: 257%;
  width: 150%;
  padding-left: 0.3rem;
}

</style>