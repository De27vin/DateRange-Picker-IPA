<template>
  <div>
    <div class="w-full">
      <div class="w-full block lg:flex items-center mb-4">
        <div class="h-24 bottom-underline flex flex-grow justify-between bg-white bg-opacity-60 justify-between items-center">
          <div class="flex max-w-4xl gap-4 p-2 pr-4 items-center">


            <div v-for="(active, tab) in searchTabs" :key="tab">
              <button v-if="active"
                  @click="toggleSearchTab(tab)"
                  type="submit" class="text-white text-sm" style="background-color: #8fabdd;"
              >
                {{ tab }}
              </button>

              <button v-else
                  @click="toggleSearchTab(tab)"
                  type="submit" class="bg-white text-gray-900 text-sm"
              >
                {{ tab }}
              </button>
            </div>

            <div class="ml-8">
              <SearchCounter :icon="counterIcon" />
            </div>
          </div>

          <div class="flex items-center mx-5 pl-12" style="width: 54%;">
            <button type="submit" class="bg-gray-400 text-gray-200 hover:text-white text-sm hover:bg-gray-600 text-center" style="background-color: #8fabdd;" @click="showFilters = !showFilters">
              Filters
            </button>

            <div class="w-full flex items-center justify-center relative ml-5 mr-6">
              <div class="absolute ml-4 left-0">
                <SelectCombobox v-if="filters.search_selected" :options="searchOptions" v-model="filters.search_selected" />
              </div>
              <input class="searchfield" ref="searchInput" type="text" :value="filters.search" @input="updateSearchValue" style="padding-left:4.5rem; height: 45px; box-shadow: none;">
              <span class="absolute right-5 text-gray-400 block w-6 h-6">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20px" height="20px" viewBox="0 0 20 20" version="1.1"><title>search</title><g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round"><g id="Artboard" transform="translate(-1687.000000, -1941.000000)" stroke="currentColor" stroke-width="2"><g id="search" transform="translate(1688.000000, 1942.000000)"><circle id="Oval" cx="7.5" cy="7.5" r="7.5"/><path d="M18 18l-5.2-5.2" fill="currentColor" id="Shape"/></g></g></g></svg>
              </span>
            </div>

            <!-- Export Menu -->
            <div v-if="showMenu" class="boxitemDropdown z-20">
              <span class="h-6" >
                <button @click="showExportMenu = !showExportMenu" class="absolute inline-flex items-center justify-center p-2 text-gray-400 hover:text-gray-500">
                   <span class="tt">
                      <i class="f7-icons icon icon-sm tts cursor-pointer" style="color: white; background-color: #8faadc;">{{ 'square_arrow_down' }}</i>
                      <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2; text-transform: none; color: #334155;">{{ 'Exports' }}</span>
                  </span>
                </button>

                <div v-if="showExportMenu"
                     class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                     @click.away="showExportMenu = false">
                  <div class="py-1">
                    <a href="#"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                       @click.prevent="dispatchExport('toggle-export')">
                      {{ trans('Export current list') }} ...
                    </a>
                    <a href="#"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                       @click.prevent="dispatchExport('toggle-export-comments')">
                      {{ trans('Export comments of listed devices') }} ...
                    </a>
                  </div>
                </div>
              </span>
            </div>

            <!-- Create Site Button -->
            <a v-if="showCreateSite" href="/devices-site-create" style="margin-left: 0.3rem;">
              <span class="tt">
                <i class="f7-icons icon icon-sm tts cursor-pointer" style="color: white; background-color: #8faadc;">plus</i>
                <span class="ttt elip ttt-tl bg-white border border-slate-300 text-dark shadow-md text-sm" style="width: max-content; zoom: 1.2;">{{ trans('Create Site') }}</span>
              </span>
            </a>

          </div>
        </div>
      </div>
    </div>

    <!-- ALERTS GRIDW -->
    <div class="grid grid-flow-col alert-grids gap-x-4 pb-12" v-if="showFilters && groupedAlertsCounts.visible && filters && filters.alerts" style="margin: auto; width: 97%;">
      <div
        v-for="(typeCount, type) in sortedVisibleAlerts"
        :key="type"
        :class="[
          'py-2 px-0 flex items-center justify-between',
          typeCount === 0 ? 'appearance-none disabled opacity-30' : ''
        ]"
      >
        <div class="flex flex-row">
          <div
            class="btn switch"
            :class="[
              filters.alerts[type] && typeCount > 0
                ? (groupedAlertsCounts.critical && groupedAlertsCounts.critical.hasOwnProperty(type) ? 'active bg-red-400' : 'active bg-warning-400')
                : (groupedAlertsCounts.critical && groupedAlertsCounts.critical.hasOwnProperty(type) ? 'bg-red-400 bg-opacity-40' : 'bg-warning-400 bg-opacity-40')
            ]"
            :aria-checked="true"
            :aria-describedby="'privacy-option-1-description'"
            :aria-labelledby="'privacy-option-1-label'"
            role="switch"
            @click.prevent.stop="typeCount > 0 && toggleFilterAlert(type)"
          >
            <span
              :class="[
                filters.alerts[type] && typeCount > 0 ? 'translate-x-5' : 'translate-x-0',
                'inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200'
              ]"
              aria-hidden="true"
            ></span>
          </div>
          <p class="ml-4 text-sm text-secondary-600 text-left" id="privacy-option-1-description">
            {{ alertsTranslations[type] }}
          </p>
        </div>
        <div class="flex flex-row justify-end">
          <p class="text-sm text-medium text-secondary-600 text-right w-10" id="privacy-option-1-label">
            {{ typeCount }}
          </p>
        </div>
      </div>
    </div>
    <!-- ALERTS GRIDW -->

    <!-- SORTOWANIE I RESET -->
    <div v-if="showFilters" class="sort-panel relative flex space-x-8 justify-end h-9"  style="margin: 35px;">
      <!-- SORT -->
      <div class="flex sort-group">
        <div class="text-white text-bold flex items-center">
          <SortDropdown
            v-model="filters.sortedby"
            :options="sortOptions"
            :placeholder="trans('Select a sort')"
          />
        </div>
        <span class="button-group h-9 inline-flex">
          <span
            @click="setSortDirection('asc')"
            :class="['chevron-dev', 'h-9 w-8 pt-3 ml-1 flex items-center justify-center cursor-pointer', filters.sortDirection === 'asc' ? 'active' : '']"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
            </svg>
          </span>
          <span
            @click="setSortDirection('desc')"
            :class="['chevron-dev', 'h-9 w-8 pt-2 ml-1 flex items-center justify-center cursor-pointer', filters.sortDirection === 'desc' ? 'active' : '']"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </span>
        </span>
        <span class="h-9 w-5 bg-gray-400 rounded-r-full" style="margin-left: 2px;"></span>
      </div>
      <!-- SORT -->

      <!-- RESET -->
      <div class="flex items-center">
        <button
          @click="resetFilters"
          class="uppercase justify-center text-medium no-underline inline-flex items-center rounded-full cursor-pointer text-white bg-gray-400 hover:bg-gray-700 focus:bg-gray-700 text-sm py-2 px-6 shadow"
        >
          {{ trans('Reset Filters') }}
        </button>
      </div>
      <!-- RESET -->
    </div>
    <!-- SORTOWANIE I RESET -->

  </div>
</template>



<script>
import { isEmpty, shiftValue } from "../assets/js/globalUtils"
import axios from "axios"
import SearchCounter from './SearchCounter.vue'
import SelectCombobox from './SelectCombobox.vue';
import SortDropdown from './SortDropdown.vue';

export default {
  props: {
    showMenu: {
      type: Boolean,
      default: false,
    },
    showCreateSite: {
      type: Boolean,
      default: false,
    },
    filtersId: {
      type: String,
      default: 'Dashboard'
    },
    counterIcon: {
      type: String,
      default: 'tag'
    },
  },

  components: {
    SearchCounter,
    SelectCombobox,
    SortDropdown
  },

  data() {
    return {
      filters: {},
      searchTabs: {},
      groupedAlertsCounts: {},
      showFilters: false,
      alertsTranslations: {},
      searchOptions: [],
      translations: {},
      // debouncing / throttling helpers
      pendingEmitTimeout: null,
      lastEmitTime: 0,
      lastEmittedState: '',
      // adaptive throttle parameters
      baseDelay: 800,
      currentDelay: 800,
      maxDelay: 1500,
      showExportMenu: false,
      filtersInitialized: false,
      // actions: [],
      // Separate debounce for search input
      searchDebounceTimeout: null,
      searchDebounceDelay: 400
    }
  },

  computed: {
    // Helper flag – allows us łatwo odróżnić dwa tryby bez rozrzucania magicznych stringów
    isDashboard() {
      return this.filtersId === 'Dashboard'
    },
    sortedVisibleAlerts() {
      if (!this.groupedAlertsCounts.visible) return {};
      return Object.fromEntries(
        Object.entries(this.groupedAlertsCounts.visible).sort(([a], [b]) => a.localeCompare(b))
      );
    },
    
    // Alerty typu alarm (dla taba 'alarms')
    alarmalityAlerts() {
      // Użyj danych z groupedAlertsCounts.alarming zamiast osobnego endpointu
      return this.groupedAlertsCounts.alarming ? Object.keys(this.groupedAlertsCounts.alarming) : [];
    },
    sortOptions() {
      return this.filters.sort_options || {};
    }
  },

  watch: {
    'filters.search': {
      handler: function(newVal, oldVal) {
        if (newVal !== oldVal) {
          this.handleSearchInput(newVal);
        }
      },
      deep: true
    },
    'filters.search_selected': {
      handler: function(newVal, oldVal) {
        if (JSON.stringify(newVal) !== JSON.stringify(oldVal) && this.filters.search && this.filters.search.trim()) {
          this.emitFiltersUpdate();
        }
      },
      deep: true
    },
    'filters.sortedby': {
      handler: function(newVal, oldVal) {
        if (newVal !== oldVal) {
          this.emitFiltersUpdate();
        }
      }
    },
    'filters.sortDirection': {
      handler: function(newVal, oldVal) {
        if (newVal !== oldVal) {
          this.emitFiltersUpdate();
        }
      }
    },
  },

  methods: {
    async fetchInitialFiltersData() {
      try {
        const idParam = this.filtersId;
        const tabsEndpoint = '/filters/' + idParam.toLowerCase() + '-default';

        let [filtersRes, alertsCountRes, searchTabsRes] = await Promise.all([
          axios.get('/filters/filters?filtersId=' + idParam),
          axios.get('/filters/grouped-alerts-counts'),
          axios.get(tabsEndpoint)
        ]);
        
        // Set the data
        this.filters = filtersRes.data;
        this.groupedAlertsCounts = alertsCountRes.data;
        this.searchTabs = searchTabsRes.data;

        if (this.isDashboard) {
          this.setAlertFiltersByActiveTabs();
        } else {
          this.filters.search_tabs = Object.keys(this.searchTabs).filter(k => this.searchTabs[k]);
        }

        // Backend now provides proper defaults, no need to set them manually

        // Set flag BEFORE dispatching initial event to handle immediate pings
        this.filtersInitialized = true;
        
        // Dispatch initial filters event
        window.dispatchEvent(new CustomEvent('filtersChanged', { 
          detail: {
            filters: this.filters,
            searchTabs: this.searchTabs
          }
        }));

        console.log('ALERTY');
        console.table(this.filters.alerts);           // zobacz wartości
        console.log(typeof this.filters.alerts.ALARM)

        this.lastEmittedState = JSON.stringify({
          filters: this.filters,
          searchTabs: this.searchTabs,
        });
        this.lastEmitTime = Date.now();

        // Ensure text filter synchronization on initialization (fixes browser back button issue)
        this.$nextTick(() => {
          // Force DOM input value to match Vue data
          if (this.$refs.searchInput && this.filters.search) {
            this.$refs.searchInput.value = this.filters.search;
            this.handleSearchInput(this.filters.search);
          }
        });

      } catch (error) {
        console.error('Error fetching initial filters data:', error);
      }
    },

    emitFiltersUpdate(ping = false, forceImmediate = false) {
      const NOW = Date.now();

      if (!this.filtersInitialized) {
        return
      }


      const sendFiltersUpdate = () => {
        const currentState = JSON.stringify({
          filters: this.filters,
          searchTabs: this.searchTabs,
        });

        // Do not emit if nothing has actually changed
        if (currentState === this.lastEmittedState && !ping) {
          return
        }

        window.dispatchEvent(
          new CustomEvent('filtersChanged', {
            detail: {
              filters: this.filters,
              searchTabs: this.searchTabs,
            },
          }),
        );
        console.log('FILTRY EMIT EVENT at ' + Date.now());

        this.lastEmitTime   = Date.now();
        this.lastEmittedState = currentState;

        // reset delay back to base after successful emit
        this.currentDelay = this.baseDelay;
      };

      const timeSinceLastEmit = NOW - this.lastEmitTime;

      // Pings should bypass throttling since they're responses to component requests
      if (ping || (timeSinceLastEmit > this.currentDelay && !this.pendingEmitTimeout)) {
        sendFiltersUpdate();
      } else {

        if (this.pendingEmitTimeout) {
          clearTimeout(this.pendingEmitTimeout);
        }

        this.pendingEmitTimeout = setTimeout(() => {
          sendFiltersUpdate();
          this.pendingEmitTimeout = null;
        }, this.currentDelay);


        this.currentDelay = Math.min(Math.round(this.currentDelay * 1.2), this.maxDelay);
      }
    },

    async fetchSearchOptions() {
      try {
        const response = await axios.get('/filters/search-options');
        this.searchOptions = response.data;
      } catch (error) {
        console.error('Error fetching search options:', error);
      }
    },
    async fetchDefaultSearchTabs() {
      try {
        const response = await axios.get('/filters/' + this.filtersId.toLowerCase() + '-default')
        this.searchTabs = response.data
      } catch (error) {
        console.error('Error fetching dashboard tabs:', error)
      }
    },

    // async fetchFilters() {
    //   try {
    //     const response = await axios.get('/filters/filters?filtersId=' + this.filtersId)
    //     console.dir(response.data)
    //     this.filters = response.data
    //
    //     // Ensure default values for sorting
    //     if (!this.filters.sortedby) {
    //       this.filters.sortedby = (this.filtersId === 'Equipment') ? 'ds_name' : 'device_equipment';
    //     }
    //     if (!this.filters.sortDirection) {
    //       this.filters.sortDirection = 'asc';
    //     }
    //   } catch (error) {
    //     console.error('Error fetching filters:', error)
    //   }
    // },

    async fetchGroupedAlertsCounts() {
      try {
        const response = await axios.get('/filters/grouped-alerts-counts')
        console.dir(response.data)
        this.groupedAlertsCounts = response.data
      } catch (error) {
        console.error('Error fetching grouped alerts counts:', error)
      }
    },

    async fetchAlertsTranslations() {
      try {
        const response = await axios.get('/filters/alerts-translations')
        console.dir(response.data)
        this.alertsTranslations = response.data
      } catch (error) {
        console.error('Error fetching alerts translations:', error)
      }
    },

    toggleSearchTab(tab) {
      // Ustaw tylko jeden tab jako aktywny
      Object.keys(this.searchTabs).forEach(searchTab => {
        this.searchTabs[searchTab] = (searchTab === tab);
      });
      
      if (this.isDashboard) {
        // Dashboard: taby wpływają na alerty
        this.setAlertFiltersByActiveTabs();
      } else {
        // Equipment: aktualizuj filters.search_tabs zgodnie z aktywnym tabem
        this.filters.search_tabs = Object.keys(this.searchTabs).filter(k => this.searchTabs[k]);
      }
      
      // Emit filters update
      this.emitFiltersUpdate();
    },

    setAlertFiltersByActiveTabs() {
      if (!this.filters.alerts) return;

      this.alarmalityAlerts.forEach(alert => {
        if (this.filters.alerts.hasOwnProperty(alert)) {
          this.filters.alerts[alert] = this.searchTabs.alarms;
        }
      });

      // Ustaw PERIODICAL na podstawie taba 'overdues'
      if (this.filters.alerts.hasOwnProperty('PERIODICAL')) {
        this.filters.alerts.PERIODICAL = this.searchTabs.overdues;
      }

      Object.keys(this.filters.alerts).forEach(alert => {
        if (!this.alarmalityAlerts.includes(alert) && alert !== 'PERIODICAL') {
          this.filters.alerts[alert] = this.searchTabs.alerts;
        }
      });
    },

    toggleFilterAlert(type) {
      if (!this.filters.alerts || !this.filters.alerts.hasOwnProperty(type)) return;

      // Zmień stan alertu
      this.$set(this.filters.alerts, type, !this.filters.alerts[type]);

      // Dashboard: kliknięcie alertu może zmieniać taby
      if (this.isDashboard) {
        this.updateTabsByAlerts();
      }
      
      // Emit filters update
      this.emitFiltersUpdate();
    },

    updateTabsByAlerts() {
      if (!this.filters.alerts) return;

      // Sprawdź czy wszystkie alerty typu alarm są wybrane
      const allAlarmsSelected = this.alarmalityAlerts.every(alert => 
        this.filters.alerts.hasOwnProperty(alert) && this.filters.alerts[alert]
      );
      const noAlarmsSelected = this.alarmalityAlerts.every(alert => 
        !this.filters.alerts.hasOwnProperty(alert) || !this.filters.alerts[alert]
      );

      // Sprawdź czy wszystkie pozostałe alerty są wybrane
      const otherAlerts = Object.keys(this.filters.alerts).filter(alert => 
        !this.alarmalityAlerts.includes(alert) && alert !== 'PERIODICAL'
      );
      const allAlertsSelected = otherAlerts.every(alert => this.filters.alerts[alert]);
      const noAlertsSelected = otherAlerts.every(alert => !this.filters.alerts[alert]);

      // Zaktualizuj taby
      this.searchTabs.overdues = this.filters.alerts.PERIODICAL || false;
      
      if (allAlarmsSelected) this.searchTabs.alarms = true;
      if (noAlarmsSelected) this.searchTabs.alarms = false;
      
      if (allAlertsSelected) this.searchTabs.alerts = true;
      if (noAlertsSelected) this.searchTabs.alerts = false;

      // Tab 'all' jest aktywny gdy żadne alerty nie są wybrane
      this.searchTabs.all = !Object.values(this.filters.alerts).some(active => active);
    },

    setSortDirection(direction) {
      this.filters.sortDirection = direction;
      this.emitFiltersUpdate();
    },

    resetFilters() {
      // Reset filters to default values using backend-provided defaults
      if (this.filters.sort_options) {
        const sortKeys = Object.keys(this.filters.sort_options);
        this.filters.sortedby = sortKeys.length > 0 ? sortKeys[0] : 'device_equipment';
      } else {
        this.filters.sortedby = (this.filtersId === 'Equipment') ? 'ds_name' : 'device_equipment';
      }
      this.filters.sortDirection = 'asc';
      this.filters.search = '';
      this.filters.search_selected = ['all'];
      
      // Reset all alerts to false
      if (this.filters.alerts) {
        Object.keys(this.filters.alerts).forEach(alert => {
          this.filters.alerts[alert] = false;
        });
      }
      
      // Fetch default search tabs from backend to maintain correct order
      this.fetchDefaultSearchTabs().then(() => {
        if (this.isDashboard) {
          this.setAlertFiltersByActiveTabs();
        } else {
          this.filters.search_tabs = Object.keys(this.searchTabs).filter(k => this.searchTabs[k]);
        }
        
        // Emit the changes
        this.emitFiltersUpdate();
      });
    },

    trans(key) {
      return this.translations?.[key] || this.alertsTranslations?.[key] || key;
    },

    async fetchTranslations() {
      try {
        const response = await axios.get('/data/translations');
        this.translations = response.data;
      } catch (error) {
        console.error('Error fetching translations:', error);
      }
    },

    dispatchExport(eventName) {
      window.dispatchEvent(new CustomEvent(eventName));
      this.showExportMenu = false;
    },
    
    handleFiltersPing() {
      console.log('FILTRY DOSTALY PING at ' + Date.now())
      this.emitFiltersUpdate(true);
    },

    updateSearchValue(event) {
      // Update the filters data directly
      this.filters.search = event.target.value;
      // Trigger the existing search handling
      this.handleSearchInput(event.target.value);
    },

    handleSearchInput(newValue) {
      // Clear any existing search debounce timeout
      if (this.searchDebounceTimeout) {
        clearTimeout(this.searchDebounceTimeout);
      }

      // If search is empty, emit immediately
      if (!newValue || newValue.trim() === '') {
        this.emitFiltersUpdate();
        return;
      }

      // Set up new debounce
      this.searchDebounceTimeout = setTimeout(() => {
        this.emitFiltersUpdate();
      }, this.searchDebounceDelay);
    },

    handlePageShow(event) {
      // Only handle when page was restored from bfcache (browser back/forward)
      if (event.persisted && this.filtersInitialized) {
        // Force text filter synchronization when returning from browser cache
        if (this.$refs.searchInput && this.filters.search) {
          this.$refs.searchInput.value = this.filters.search;
          this.handleSearchInput(this.filters.search);
        }
      }
    },

  },

  created() {
    // window.addEventListener('loading', this.handleLoading)
    this.fetchTranslations()
    this.fetchInitialFiltersData()
    this.fetchAlertsTranslations()
    // this.fetchGroupedAlertsCounts()
    this.fetchSearchOptions();

    window.addEventListener('filtersPing', this.handleFiltersPing);
    
    // Handle browser back/forward navigation from cache (bfcache)
    window.addEventListener('pageshow', this.handlePageShow);
  },

  destroyed() {
    // window.removeEventListener('loading', this.handleLoading)

    window.removeEventListener('filtersPing', this.handleFiltersPing);
    window.removeEventListener('pageshow', this.handlePageShow);
    
    // Clean up search debounce timeout
    if (this.searchDebounceTimeout) {
      clearTimeout(this.searchDebounceTimeout);
    }
  }
}
</script>



<style>
.chevron-dev {
  background-color: #94a3b8 !important;
}
.chevron-dev.active {
  background-color: #9398a4 !important;
}



.icon-wrapper {
  margin-left: 1rem;
}

.icon {
  padding: 0.1rem;
  border: solid 2px #94a3b8;
  border-radius: 0.2rem;
  width: 1.5rem;
  height: 1.5rem;
}

.icon.default {
  background-color: lightblue;
}

.icon-sm {
  font-size: 1rem;
}

.icon-md {
  font-size: 1.3rem;
}

.icon-lg {
  font-size: 1.6rem;
}
</style>
