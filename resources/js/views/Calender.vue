<template>
    <div
        style="
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 20px;
        "
    >
        <!-- Dropdown to select between Sportma and Manager -->
        <!-- <div class="user-selection">
      <label for="user">Select User:</label>
      <select v-model="selectedUser">
        <option value="sportma">Sportma</option>
        <option value="manager">Manager</option>
      </select>
    </div> -->

        <!-- Radio buttons for selecting sport -->
        <div class="sport-selection">
            <label v-for="sport in sports" :key="sport">
                <input
                    type="radio"
                    v-model="selectedSport"
                    :value="sport"
                    :key="selectedSport"
                    @change="updateSplits"
                />
                {{ sport.charAt(0).toUpperCase() + sport.slice(1) }}
            </label>
        </div>

        <!-- VueCal Calendar -->
        <vue-cal
            ref="vuecal2"
            locale="fr"
            style="height: 550px; width: 100%"
            hide-view-selector
            active-view="day"
            :time-step="timeStep"
            :time-cell-height="timeCellHeight"
            :disable-views="['years', 'year', 'week']"
            :drag-to-create-event="false"
            show-time-in-cells
            :snap-to-time="15"
            :editable-events="{ title: false, drag: false, resize: true, delete: false, create: true }"
            :events="events"
            :split-days="splitDays"
            :min-cell-width="minCellWidth"
            :min-event-width="minEventWidth"
            :overlaps-per-time-step="overlapsPerTimeStep"
            @ready="scrollToCurrentTime"
            sticky-split-labels
            @cell-dblclick="createEventInSplit"
            :on-event-dblclick="onEventClick"
            @event-create="onEventCreate"
            @event-drop="dropEvent"
            @event-duration-change="dragEvent"
            @event-delete="stopDeleteEvent"
            @event-title-change="logEvents('event-title-change', $event)"
        >
            <!-- Split Label Template -->
            <template #split-label="{ split, events, view }">
                <strong :style="`color: ${split.color}`">{{
                    split.label
                }}</strong>
                <div class="vuecal__event-title" style="display: none" />
            </template>

            <!-- Event Template -->
            <template #event="{ event, view }">
                <div style="cursor: grab">
                    <v-icon>{{ event.icon }}</v-icon>
                    <span class="top-right-text"
                        ><span class="material-symbols-outlined">
                            pan_tool
                        </span>
                    </span>

                    <!-- <div class="vuecal__event-title" v-html="event.title" /> -->
                    <!-- Or if your events are editable: -->
                    <div
                        class="vuecal__event-title "
                        style="cursor: pointer"
                        @blur="event.title = $event.target.innerHTML"
                        v-html="event.title"
                    />

                    <div class="vuecal__event-time">
                        <!-- Using Vue Cal Date prototypes (activated by default) -->
                        <span>{{ event.start.formatTime("HH:mm") }} - </span>
                        <span>{{ event.end.formatTime("HH:mm") }}</span>
                    </div>
                    <div
                        class="vuecal__event-content text-top-left"
                        v-html="event.content"
                    />
                </div>
            </template>
        </vue-cal>
    </div>

    <v-dialog v-model="dialog" max-width="400px">
        <v-card>
            <!-- Title -->
            <v-card-title class="headline">Changer Le Statut</v-card-title>

            <!-- Status Selection -->
            <v-card-text>
                <div class="event-content">
                    <select
                        v-model="selectedStatus"
                        @input="handleInput"
                        style="
                            width: 100%;
                            height: 50px;
                            padding: 10px;
                            border: 2px solid #007bff;
                            border-radius: 4px;
                            outline: none;
                        "
                    >
                        <option disabled value="disabled">
                            Sélectionner le statut
                        </option>
                        <option value="Payé" data-class="green-event">
                            🟢 Payé
                        </option>
                        <option value="En-cours" data-class="yellow-event">
                            🟡 en-cours
                        </option>
                        <option value="Annulé" data-class="red-event">
                            🔴 Annulé
                        </option>
                    </select>
                </div>

                <!-- Title Input -->
                <v-text-field
                    label="Titre"
                    v-model="selectedEventTitle"
                    outlined
                    style="margin-top: 20px"
                ></v-text-field>

                <!-- Price Input -->
                <v-text-field
                    label="Prix"
                    v-model="selectedEvent.price"
                    outlined
                    style="margin-top: 20px"
                    type="number"
                ></v-text-field>

                <!-- Remarks Text Area for event.notes -->
                <v-textarea
                    label="Remarques"
                    v-model="selectedEvent.notes"
                    rows="3"
                    outlined
                    style="margin-top: 20px"
                ></v-textarea>
            </v-card-text>

            <!-- Actions -->
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="red darken-1" text @click="deleteEvent"
                    >Supprimer</v-btn
                >
                <!-- Delete Button -->
                <v-btn color="blue darken-1" text @click="confirm"
                    >Confirmer</v-btn
                >
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import { v4 as uuidv4 } from "uuid";
import { mapState, mapGetters, mapMutations, mapActions } from "vuex";

import VueCal from "vue-cal";
import moment from "moment";
import { extendMoment } from "moment-range";

import "vue-cal/dist/vuecal.css";

const momentRange = extendMoment(moment);

export default {
    components: {
        VueCal,
    },
    data() {
        return {
            timeCellHeight: 80, // Height of one hour in pixels
            now: new Date(), // Get the current time
            selectedUser: "manager", // Default selected user
            selectedSport: null, // Default selected sport
            eventAction: null,
            stickySplitLabels: true,
            minCellWidth: 300,
            minSplitWidth: 300,
            minEventWidth: 100,
            timeCellHeight: 65,
            timeStep: 30,
            splitDays: [], // To be updated based on selected sport
            overlapsPerTimeStep: true,
            dialog: false,
            selectedEvent: null,
            selectedStatus: "disabled",
            selectedEventClass: null,
            selectedEventTitle: null,
            updatedEvents: this.events || [],
        };
    },
    computed: {
        ...mapGetters("calendarConfig", ["splitTypes"]),
        ...mapState("calendar", ["events"]),
        ...mapGetters("calendar", ["getEvents"]),
        sports() {
            return this.splitTypes
                ? this.splitTypes.map((split) => split.type)
                : [];
        },
    },
    watch: {
        sports(newSports) {
            if (newSports.length) {
                this.selectedSport = newSports[0];
            }
        },
    },
    mounted() {
        console.log(this.updatedEvents);
        if (this.sports.length && !this.selectedSport) {
            this.selectedSport = this.sports[0];
        }
        this.updateSplits(); // Initialize splitDays on mount
    },
    methods: {
        // Mapping mutations to modify the events
        ...mapMutations("calendar", ["SET_EVENTS"]),

        logEvents(event, data) {
            console.log(event, data);
        },
        stopDeleteEvent(event) {
            if (!event.clickable) {
                alert("Cet event ne peut pas être supprimé");
                this.updatedEvents.push(event);
                this.SET_EVENTS(this.updatedEvents);
                return;
            }
        },
        checkForCreationOverlapping(newEvent) {
            let duration = 60; // Default duration
            let newEventStart = moment(this.snapToNearest1h(newEvent.date));
            // Check if event.split contains the word "padel"
            if (newEvent.split.toLowerCase().includes("padel")) {
                newEventStart = moment(this.snapToNearest30(newEvent.date));
                duration = 90;
            }
            const newEventEnd = moment(newEventStart).add(duration, "minute"); // assuming duration is 1 hour, adjust this as needed

            const newEventRange = momentRange.range(newEventStart, newEventEnd);

            const isOverlap = this.updatedEvents.some((event) => {
                // Check if the event is in the same split
                if (event.split === newEvent.split) {
                    const existingEventStart = moment(event.start);
                    const existingEventEnd = moment(event.end);
                    const existingEventRange = momentRange.range(
                        existingEventStart,
                        existingEventEnd
                    );

                    // Check if the new event overlaps with any existing event
                    return newEventRange.overlaps(existingEventRange);
                }
                return false; // No overlap if in different splits
            });

            // Display an alert if the new event overlaps with an existing event
            if (isOverlap) alert("Cet event ne peut pas être créé");

            return isOverlap;
        },
        isOverlapping(newEvent) {
            return this.updatedEvents.some((event) => {
                // Exclude the newEvent itself by comparing IDs (or other unique properties)
                if (event.id === newEvent.event.id) {
                    return false; // Skip the current newEvent in the comparison
                }

                // Create a range for the existing event
                const existingEventRange = momentRange.range(
                    moment(event.start),
                    moment(event.end)
                );

                // Create a range for the new event
                const newEventRange = momentRange.range(
                    moment(newEvent.event.start),
                    moment(newEvent.event.end)
                );

                if (event.split === newEvent.event.split) {
                    // Check if the ranges overlap
                    return newEventRange.overlaps(existingEventRange);
                } else return false;
            });
        },

        dragEvent(event) {
            this.eventAction = "drag";
            this.dropEvent(event);
        },
        dropEvent(newEvent) {
            // Check for overlapping only if event.split and newEvent.newSplit are the same
            const isOverlap = this.updatedEvents.some((event) => {
                // Exclude the newEvent by comparing unique identifiers
                if (event.id !== newEvent.event.id) {
                    // Check for overlapping only if the split values match
                    if (event.split === newEvent.event.split) {
                        return this.isOverlapping(newEvent, event);
                    } else return false;
                }
            });

            if (isOverlap) {
                alert(
                    "This event overlaps with an existing event in the same split. Please choose a different time."
                );
                this.addEventToEvents(newEvent);
                return;
            }
            // Replace the event in events array with newEvent.event if they have the same id
            this.updatedEvents = this.updatedEvents.map((event) => {
                if (event.id === newEvent.event.id) {
                    // Parse the start and end times as Date objects
                    const startTime = new Date(newEvent.event.start);
                    const endTime = new Date(newEvent.event.end);

                    // Calculate the difference in minutes
                    const durationInMinutes =
                        (endTime - startTime) / (1000 * 60);

                    // Update the duration property
                    newEvent.event.duration = durationInMinutes;

                    return newEvent.event;
                } else {
                    return event;
                }
            });

            this.SET_EVENTS(this.updatedEvents);
        },

        addEventToEvents(eventObj) {
            // Delete the event with the same id as eventObj.event.id from the events array
            this.updatedEvents = this.updatedEvents.filter(
                (event) => event.id !== eventObj.event.id
            );

            // Clone the event object to avoid modifying the original
            const newEvent = { ...eventObj.originalEvent };

            if (this.eventAction === "drag") {
                newEvent.start = new Date(newEvent.start);
                newEvent.end = new Date(newEvent.end);
                this.updatedEvents.push(newEvent);
                this.SET_EVENTS(this.updatedEvents);
                this.eventAction = "";
                return;
            }

            // Set the start to the oldDate as a Date object
            newEvent.start = new Date(eventObj.oldDate);

            // Calculate the end by adding the duration (in minutes) to the start
            newEvent.end = new Date(
                newEvent.start.getTime() + newEvent.duration * 60000
            ); // 60000 ms in a minute

            // Add the new event to the events array
            this.updatedEvents.push(newEvent);

            this.SET_EVENTS(this.updatedEvents);
        },
        onEventCreate(newEvent) {
            if (newEvent.split) {
                if (newEvent.split.toLowerCase().includes("football")) {
                    // Adjust start and end times to nearest 30-minute interval
                    newEvent.start = this.snapToNearest1h(newEvent.start);

                    // make newEvent.end adds duration to newEvent.start
                    // newEvent.end = new Date(
                    //   newEvent.start.getTime() + newEvent.duration * 60000
                    // )
                    newEvent.end = this.snapToNearest1h(newEvent.end);
                } else if (newEvent.split.toLowerCase().includes("padel")) {
                    newEvent.start = this.snapToNearest30(newEvent.start);
                    newEvent.end = this.snapToNearest30(newEvent.end);
                }
            }

            // Now add the adjusted event to the calendar
            this.updatedEvents.push(newEvent);
            this.SET_EVENTS(this.updatedEvents);
        },

        snapToNearest1h(date) {
            const d = new Date(date);
            const minutes = d.getMinutes();

            if (minutes < 30) {
                // Snap to the start of the hour
                d.setMinutes(0);
            } else {
                // Snap to the start of the next hour
                d.setMinutes(0);
                d.setHours(d.getHours() + 1);
            }

            d.setSeconds(0, 0); // Reset seconds and milliseconds
            return d;
        },
        snapToNearest30(date) {
            const d = new Date(date);
            const minutes = d.getMinutes();

            if (minutes < 15) {
                // Snap to the start of the hour
                d.setMinutes(0);
            } else if (minutes < 45) {
                // Snap to the start of the next 30 minutes
                d.setMinutes(30);
            } else {
                // Snap to the start of the next hour
                d.setMinutes(0);
                d.setHours(d.getHours() + 1);
            }

            d.setSeconds(0, 0); // Reset seconds and milliseconds
            return d;
        },
        handleInput(event) {
            this.selectedStatus = event.target.value;

            // Update the class relative to the selected status
            if (this.selectedStatus === "Annulé") {
                this.selectedEventClass = "red" + "-event";
            } else if (this.selectedStatus === "En-cours") {
                this.selectedEventClass = "yellow" + "-event";
            } else if (this.selectedStatus === "Payé") {
                this.selectedEventClass = "green" + "-event";
            }
        },
        open() {
            this.dialog = true;
        },
        close() {
            this.dialog = false;
        },
        confirm() {
            this.selectedEvent.statut = this.selectedStatus;
            this.selectedEvent.class = this.selectedEventClass;
            this.selectedEvent.title = this.selectedEventTitle;
            // Find and replace the event with the same id
            const eventIndex = this.updatedEvents.findIndex(
                (event) => event.id === this.selectedEvent.id
            );

            if (eventIndex !== -1) {
                // Replace the old event with the updated selectedEvent
                this.updatedEvents[eventIndex] = { ...this.selectedEvent }; // Spread syntax to replace the object

                this.SET_EVENTS(this.updatedEvents);
            } else {
                this.updatedEvents.push(this.selectedEvent);
                this.SET_EVENTS(this.updatedEvents);
            }

            // Close the dialog
            this.close();
        },

        deleteEvent() {
            // Find the index of the event with the same id
            const eventIndex = this.updatedEvents.findIndex(
                (event) => event.id === this.selectedEvent.id
            );

            if (eventIndex !== -1) {
                // Remove the event from the array
                this.updatedEvents.splice(eventIndex, 1);

                this.SET_EVENTS(this.updatedEvents);
            } else {
                console.log("Event not found, could not delete.");
            }

            this.SET_EVENTS(this.updatedEvents);

            // Close the dialog
            this.close();
        },
        updateStatus() {
            // This method can be used if you want to react to changes in the select input
            console.log("Selected status changed to:", this.selectedStatus);
        },
        updateSplits() {
            // Retrieve the selected sport split type from the Vuex store
            const selectedSplitType = this.splitTypes.find(
                (splitType) =>
                    splitType.type.toLowerCase() ===
                    this.selectedSport.toLowerCase()
            );

            // If a matching split type is found, update the split properties
            if (selectedSplitType) {
                this.timeStep = selectedSplitType.timeStep;
                this.timeCellHeight = selectedSplitType.timeCellHeight;

                // Map each terrain to the expected format for splitDays
                this.splitDays = selectedSplitType.terrains.map(
                    (terrain, index) => ({
                        id: terrain.id,
                        label: terrain.label,
                        class: index % 2 === 0 ? "white" : "grey", // Alternating classes for styling
                    })
                );
            }
        },

        createEventInSplit(event) {
            if (!event.hasOwnProperty("split")) {
                alert(
                    "Veuillez configurer votre calendrier dans la page de configuration."
                );
                return;
            }
            // if (event.hasOwnProperty("split")) {
            // Check if event is overlapping events in the same split
            if (!this.checkForCreationOverlapping(event)) {
                if (event.split) {
                    let eventClass = "";
                    let clickable = false;
                    let editable = true;
                    let eventContent = "";
                    let duration = 60; // Default duration
                    let price = 0;
                    let newDate = new Date(event.date);

                    // Check if event.split contains the word "padel"
                    if (event.split.toLowerCase().includes("padel")) {
                        duration = 90; // Set duration to 90 if "padel" is found
                    }

                    // Decide price
                    const splitType = this.splitTypes.find((splitType) =>
                        event.split
                            .toLowerCase()
                            .includes(splitType.type.toLowerCase())
                    );

                    const matchingTerrain = splitType.terrains.find(
                        (terrain) => terrain.id === event.split
                    );

                    const prices = matchingTerrain
                        ? matchingTerrain.prices
                        : null;

                    if (splitType.type.toLowerCase() === "football") {
                        newDate = this.snapToNearest1h(newDate);
                    } else if (splitType.type.toLowerCase() === "padel") {
                        newDate = this.snapToNearest30(newDate);
                    }

                    price =
                        this.getPriceForTimeInterval(
                            newDate,
                            duration,
                            prices
                        ) || 0;

                    // Conditional content based on user type
                    if (this.selectedUser === "sportma") {
                        eventClass = "blue-event";
                        eventContent = `
        <div class="event-content">
          <img src="https://sportma.ma/assets/sportmaApp-ERXWPjF0.jpeg" width="25" class="event-icon" alt="Icon" />
        </div>
      `;
                        editable = false;
                    } else if (this.selectedUser === "manager") {
                        eventClass = "yellow-event"; // Default class for manager
                        clickable = true;
                    } else {
                        eventClass = "green-event"; // Default class for other users
                        clickable = true;
                    }

                    this.$refs.vuecal2.createEvent(event.date, duration, {
                        id: uuidv4(),
                        title: `Nouvelle Reservation`,
                        class: eventClass,
                        content: eventContent,
                        split: event.split,
                        clickable: clickable,
                        duration: duration,
                        editable: editable,
                        price: price,
                        statut: "En-cours",
                        category: splitType.type,
                        terrain: matchingTerrain.label,
                        titleEditable: false,
                        deletable: false,
                    });

                    this.SET_EVENTS(this.updatedEvents);
                }
            }
            // }
        },
        getPriceForTimeInterval(date, duration, priceRanges) {
            // Convert the provided date into hours and minutes
            const startDate = new Date(date);
            const startHour = startDate.getHours();
            const startMinute = startDate.getMinutes();

            // Calculate end time by adding the duration in minutes
            const endDate = new Date(startDate.getTime() + duration * 60000);
            const endHour = endDate.getHours();
            const endMinute = endDate.getMinutes();

            // Helper function to convert "hh:mm" to a Date object with same day as startDate
            const timeToDate = (time) => {
                const [hour, minute] = time.split(":").map(Number);
                const date = new Date(startDate);
                date.setHours(hour, minute, 0, 0);
                return date;
            };

            // Find the price range that matches the start time
            const matchingRange = priceRanges.find(({ startTime, endTime }) => {
                const rangeStart = timeToDate(startTime);
                const rangeEnd = timeToDate(endTime);

                // handle the case next day
                if (rangeEnd < rangeStart) {
                    rangeEnd.setDate(rangeEnd.getDate() + 1);
                }

                // Check if the interval falls within the price range
                return startDate >= rangeStart && endDate <= rangeEnd;
            });

            return matchingRange ? matchingRange.price : null;
        },
        onEventClick(event, e) {
            e.stopPropagation();
            if (event.clickable === true) {
                this.selectedEvent = event;
                this.selectedStatus = event.statut;
                this.selectedEventTitle = event.title;
                // Update the class relative to the selected status
                if (this.selectedStatus === "Annulé") {
                    this.selectedEventClass = "red" + "-event";
                } else if (this.selectedStatus === "En-cours") {
                    this.selectedEventClass = "yellow" + "-event";
                } else if (this.selectedStatus === "Payé") {
                    this.selectedEventClass = "green" + "-event";
                }
                this.open();
            }
        },
        scrollToCurrentTime() {
            // Access the calendar's scrollable background
            const calendar =
                this.$refs.vuecal2.$el.querySelector(".vuecal__bg");

            // Get the current hour and minutes as a decimal (e.g., 8:30 -> 8.5)
            const hours = 8;

            // If the calendar is available, scroll to the correct time
            if (calendar) {
                calendar.scrollTo({
                    top: hours * this.timeCellHeight, // Scroll to the calculated time position
                    behavior: "smooth",
                });
            }
        },
    },
};
</script>

<style>
.cell-time-labels {
    border-left: 1px solid #b3b3b3 !important;
}

.top-right-text {
    position: absolute;
    top: 0;
    right: 0;
}

.text-top-left {
    position: absolute;
    top: 0;
    left: 0;
}

.vuecal__cell .cell-time-label {
    border-top: 1px solid black !important; /* Color of the dividing line */
}

.vuecal__time-column .vuecal__time-cell {
    border-top: 1px solid #b3b3b3 !important;
}

/* Styles for the user dropdown */
.user-selection {
    margin-bottom: 15px;
}

.user-selection select {
    padding: 5px;
    border: 2px solid #007bff;
    border-radius: 4px;
    outline: none;
    font-size: 14px;
}

/* Styles for the radio buttons */
.sport-selection {
    margin-bottom: 20px;
}

.sport-selection label {
    margin-right: 15px;
    font-size: 16px;
    cursor: pointer;
}

.sport-selection input[type="radio"] {
    margin-right: 5px;
}

/* Event styles */
.blue-event {
    background-color: lightblue;
    color: white;
}

.green-event {
    background-color: lightgreen;
    color: white;
}

.red-event {
    background-color: red;
    color: white;
}

.yellow-event {
    background-color: #d6c32f;
    color: white;
}

/* Styling for sticky split labels */
.vuecal-fixed-splits .vuecal__split-label--sticky {
    position: sticky;
    top: 0;
    z-index: 1;
    background-color: white;
    border-bottom: 1px solid #eaeaea;
}
</style>
