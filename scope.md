# Community Small Group Meditation Time Tracker App
We are part of an NGO to create public awareness on keeping one self calm, relaxed and remain in Peace through the practice of meditation.

We have arranged a new In-Person campaign accordingly we encourage people to spend some time in reflecting and meditating at our branches.

We have branches all across Tamilnadu and also we have volunteers

Accordingly, this campaign with run for 4 months starting from August to November

We would like to build a small one page web app using VueJS 3 for Branch Volunteers to track the hours spent by people at their branches.

There will be a specially designed room in which people will go into the room and spend minimum 30 minutes to meditate and return. Some people may also meditate 60 minutes or 90 minutes.

Once they exit the room, the volunteer collects the Name and Duration of Meditation and adds them in the Web App.

The data will be stored through a backend API.

Here the complete flow, I am looking for.

1. Volunteers opens the web app
2. The System will ask them to Login using a Mobile Number and a Password. (This will be already shared to them via email before the campaign starts)
3. Once the credentials are validated via API, we will list all the branches in a dropdown to select. Sometimes, Same volunteer may manage multiple branches
4. Once the branch is selected, they will get a Dataentry Page to collect the details for that DAY/Date
5. They will enter the Participant Name in a textbox. This should be autocomplete with custom entry if there is no existing entries (fetched from API)
6. Then they will have a option to enter the time they meditated. Instead of showing all the hours from 7 AM to 10 PM, we will show them in 30 minutes listing. 7:00 AM, 7:30 AM, 8:00 AM etc
7. Even the listing will be filtered via Segment Filters (Morning Times, Afternoon Times, Evening Times)
8. The user will not type anything in that grid, they simply touch and span the hours spent by the Participant
9. Then they will click submit which will add an entry (via API)
10.In the Top there should be a link named "Dashboard", which will take them to a page which shows the Total list of participants and the total hours spent this month with some reports drilldown with details. All these should come from a API.


Database
1. You need to use MYSQL for the Database
2. You need to use a simple PHP for the API
3. You need to use VueJS3 for a HTML Standalone
4. No need for any build/compilation complexities
5. Keep the design theme using this color as base #04349C (blue)
6. Use tailwindcss for the design
7. Keep the design simple neat and clean as this is for Volunteers and Admin only




