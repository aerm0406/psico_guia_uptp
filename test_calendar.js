const startDate = new Date();
const endDate = new Date();
endDate.setDate(startDate.getDate() + 30);

let dCounter = new Date(startDate);
const allDays = [];
while (dCounter <= endDate) {
    allDays.push(new Date(dCounter));
    dCounter.setDate(dCounter.getDate() + 1);
}
console.log("allDays length:", allDays.length);
