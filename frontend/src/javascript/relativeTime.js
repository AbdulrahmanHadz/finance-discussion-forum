import moment from 'moment'

export function formatRelativeDate(date) {
    return moment(date, moment.ISO_8601).fromNow()
}