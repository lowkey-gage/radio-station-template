# Editing the station

Shows and DJs are their own content types. They are not blog posts. Relationships are stored on the show: open the show and choose its DJs. A DJ page then lists every show that names that DJ.

## Add a show

1. In wp-admin, open Shows → Add New.
2. Enter the title and the description in the main editor.
3. Set a featured image. Cards and the show page use it. If you skip the image, the site shows the show’s initial instead of a broken picture.
4. In the show details, set the genre and status.
5. Under DJs, select one or more hosts. Split Signal and The Long Dock are the samples with two hosts. Nova Ellison is the sample host who appears on more than one show.
6. Under Schedule, turn on each weekday the show airs and set a start and end time. Keep the end time later than the start on the same day. Open Channel is the sample that airs on more than one day.
7. In the Show Categories box, choose Music, News, Sports, or Talk.
8. Publish.

## Add a DJ

1. Open DJs → Add New.
2. The title is the on-air name. The main editor is the biography.
3. Set a featured image for the portrait.
4. Fill in the real name, favorite genre, and any public links.
5. Publish. Assign this DJ from each show they host. The profile does not have its own show checklist.

## Change a time

1. Open the show.
2. In the schedule fields, edit the start or end for that weekday, or turn the day off.
3. Update. The weekly board, On Air Now, and Coming Up Next read these fields. They use the timezone from Settings → General.

## Blocks

The block inserter has a Radio Station group with three blocks. Each one reads the stored shows and DJs. It does not store a second copy of the schedule.

- **Show Schedule** prints the week, or one weekday, grouped by day and ordered by start time. Each show name links to that show. The Schedule page includes this block.
- **DJ Information** prints a photo, name, short bio, and profile link for one DJ you pick. On a show, switch it to “DJs assigned to this show” to list that show’s hosts.
- **Show Listings** prints linked show cards. Filter by a show category, or turn on “this DJ’s related shows” when the block sits on a DJ profile.

If a photo is missing, the card uses an initial. If a filter matches nothing, the block says so instead of leaving a blank hole.

## Station text and the player

Appearance → Customize → Station identity holds the name, frequency, tagline, city, about paragraph, studio address, and contact email. Where to listen holds the request line, stream URL, and the other listen links. Listen shows an audio player only after the stream URL is saved.
