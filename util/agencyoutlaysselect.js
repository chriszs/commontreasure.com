var fs = require('fs');

var dir = "./mts"

var nametoabbrev = {
	"DEPARTMENT OF THE TREASURY: INTEREST ON THE PUBLIC DEBT":"Debt Service",
	"DEPARTMENT OF THE TREASURY: INTEREST ON TREASURY DEBT SECURITIES (GROSS)":"Debt Service",
	"DEPARTMENT OF THE TREASURY: OTHER":"Treasury Department",
	"THE JUDICIARY":"Judicial Branch",
	"DEPARTMENT OF HEALTH AND HUMAN SERVICES":"Department of Health and Human Services",
	"DEPARTMENT OF HOMELAND SECURITY":"Department of Homeland Security",
	"DEPARTMENT OF JUSTICE":"Justice Department",
	"DEPARTMENT OF LABOR":"Labor Department",
	"DEPARTMENT OF TRANSPORTATION":"Transportation Department",
	"LEGISLATIVE BRANCH":"Legislative Branch",
	"JUDICIAL BRANCH":"Judicial Branch",
	"DEPARTMENT OF DEFENSE-MILITARY PROGRAMS":"Defense Department",
	"DEPARTMENT OF ENERGY":"Energy Department",
	"DEPARTMENT OF AGRICULTURE":"Agriculture Department",
	"DEPARTMENT OF DEFENSE-MILITARY":"Defense Department",
	"DEPARTMENT OF COMMERCE":"Commerce Department",
	"DEPARTMENT OF EDUCATION":"Education Department",
	"DEPARTMENT OF THE INTERIOR":"Interior Department",
	"DEPARTMENT OF STATE":"State Department",
	"CORPS OF ENGINEERS":"Corps of Engineers",
	"OTHER DEFENSE CIVIL PROGRAMS":"Defense Civil Programs",
	"DEPARTMENT OF DEFENSE-CIVIL":"Defense Civil Programs",
	"EXECUTIVE OFFICE OF THE PRESIDENT":"President",
	"ENVIRONMENTAL PROTECTION AGENCY":"Environmental Protection Agency",
	"FEDERAL EMERGENCY MANAGEMENT AGENCY":"Federal Emergency Management Agency",
	"TOTAL OUTLAYS":"Total",
	"GENERAL SERVICES ADMINISTRATION":"General Service Administration",
	"INTERNATIONAL ASSISTANCE PROGRAM":"International Assistance Program",
	"SMALL BUSINESS ADMINISTRATION":"Small Business Administration",
	"SOCIAL SECURITY ADMINISTRATION":"Social Security Administration",
	"NATIONAL AERONAUTICS AND SPACE ADMINISTRATION":"NASA",
	"OFFICE OF PERSONNEL MANAGEMENT":"Office of Personell Management",
	"UNDISTRIBUTED OFFSETTING RECEIPTS:":"Offsetting Income",
	"UNDISTRIBUTED OFFSETTING RECEIPTS: INTEREST":"Interest Income",
	"UNDISTRIBUTED OFFSETTING RECEIPTS: OTHER":"Offsetting Income",
	"ALLOWANCES":"Allowances",
	"NATIONAL SCIENCE FOUNDATION":"National Science Foundation",
	"DEPARTMENT OF VETERANS AFFAIRS":"Department of Veterans Affairs",
	"DEPARTMENT OF HOUSING AND URBAN DEVELOPMENT":"Department of Housing and Urban Development",
	"DEPARTMENT OF THE TREASURY:":"Treasury Department",
	"OTHER INDEPENDENT AGENCIES":"Other Independent Agencies",
	"FUNDS APPROPRIATED TO THE PRESIDENT":"Special War Funding"
};

fs.readdir(dir, function (err,files) {

	var items = [];

	for (i in files) {
		var file = files[i];
		if (file.indexOf(".json") !== -1) {
			var contents = fs.readFileSync(dir + "/" + file);
			var tables = JSON.parse(contents);

			if (3 in tables && file != "mts1299.json") {
				var agencies = {};

				agencies.date = file.substr(3,2) + "/" + file.substr(5,2);

				var prefix = "";

				var table = tables[3];

				var parent = null;
				var current = null;

				var outlays = false;

				for (i in table['rows']) {
					var row = table['rows'][i];
					var name = row[0];
					var outlay = row[1];

					if (outlays) {
						if (name.indexOf(":") !== -1) {
							prefix = name + " ";
						}
						else if (name.indexOf("TOTAL OUTLAYS") !== -1) {
							name = name.trim();
						}
						else if (name.indexOf(" ") === 0) {
							name = prefix + name.trim();
						}

						if (outlay == "......") {
							outlay = 0;
						}
						else if (outlay == "(**)") {
							outlay = 0;
						}

						if (name in nametoabbrev) {
							name = nametoabbrev[name];
						}
						else {
							console.log(name);
						}

						if (outlay != "")
							agencies[name] = outlay;
					}

					if (name.indexOf("BUDGET OUTLAYS") !== -1)
						outlays = true;

					if (name.indexOf("Total") !== -1)
						outlays = false;

/*
					if ((name.indexOf(":") !== -1 && name.indexOf(" ") !== 0) || name.indexOf(" ") !== 0) {
						if (!(name.replace(":","") in items)) {
							items[name.replace(":","")] = {};
						}
						current = items[name.replace(":","")];
					} 
					*/
				}

				items.push(agencies);

			}
		}

	}

	console.log(JSON.stringify(items));

});