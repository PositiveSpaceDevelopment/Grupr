angular.module('starter.services', [])

//factory to carry around user data
.factory("ProfileData",function() {
  data = {
    "email": "aterra@smu.edu",
    "user_id": "1",
    "classes": [
      {
        "class_subject": "CSE",
        "class_number": "3330"
      },
      {
        "class_subject": "CSE",
        "class_number": "3381"
      },
      {
        "class_subject": "CEE",
        "class_number": "3302"
      },
      {
        "class_subject": "PRW",
        "class_number": "2301"
      },
      {
        "class_subject": "CSE",
        "class_number": "3353"
      },
      {
        "class_subject": "CSE",
        "class_number": "3342"
      }
    ],
    "first_name": "Andrew",
    "last_name": "Terra",
	"level": "5.4",
	"icon": "grupr-logo.png"
  }
  return {data};

  // return {data:null};
})

.factory("classes",function() {
  data = {
    "classes": [
      {
        "class_subject": "ACCT",
      },
      {
        "class_subject": "ADPR",
      },
      {
        "class_subject": "ADRE",
      },
      {
        "class_subject": "ADV",
       },
      {
        "class_subject": "AERO",
      },
      {
        "class_subject": "AMAE",
      },
	  {
        "class_subject": "ANTH",
      },
      {
        "class_subject": "APSM",
      },
      {
        "class_subject": "ARBC",
      },
      {
        "class_subject": "ARHS",
       },
      {
        "class_subject": "ASAG",
      },
      {
        "class_subject": "ASCE",
      },
	  {
        "class_subject": "ASDR",
      },
      {
        "class_subject": "ASIM",
      },
      {
        "class_subject": "ASPH",
      },
      {
        "class_subject": "ASPR",
       },
      {
        "class_subject": "ASPT",
      },
      {
        "class_subject": "ASSC",
      },
	  {
        "class_subject": "BA",
      },
      {
        "class_subject": "BAEX",
      },
      {
        "class_subject": "BB",
      },
      {
        "class_subject": "BHSC",
       },
      {
        "class_subject": "BIOL",
      },
      {
        "class_subject": "BL",
      },
	  {
        "class_subject": "BLI",
      },
      {
        "class_subject": "BSSN",
      },
      {
        "class_subject": "BUSE",
      },
      {
        "class_subject": "CA",
       },
      {
        "class_subject": "CEE",
      },
      {
        "class_subject": "CELL",
      },
	  {
        "class_subject": "CF",
      },
      {
        "class_subject": "CFA",
      },
      {
        "class_subject": "CFB",
      },
      {
        "class_subject": "CHEM",
       },
      {
        "class_subject": "CHIN",
      },
      {
        "class_subject": "CISB",
      },
	  {
        "class_subject": "CLAR",
      },
      {
        "class_subject": "CLAS",
      },
      {
        "class_subject": "CM",
      },
      {
        "class_subject": "COMM",
       },
      {
        "class_subject": "CRCP",
      },
      {
        "class_subject": "CSE",
      },
	  {
        "class_subject": "CW",
      },
      {
        "class_subject": "DANC",
      },
      {
        "class_subject": "DBBS",
      },
      {
        "class_subject": "DISC",
       },
      {
        "class_subject": "DM",
      },
      {
        "class_subject": "DNSH",
      },
	  {
        "class_subject": "DSIN",
      },
      {
        "class_subject": "ECO",
      },
      {
        "class_subject": "EDU",
      },
      {
        "class_subject": "EE",
       },
      {
        "class_subject": "EETS",
      },
      {
        "class_subject": "EMIS",
      },
	  {
        "class_subject": "ENGL",
      },
      {
        "class_subject": "ENGR",
      },
      {
        "class_subject": "ENSC",
      },
      {
        "class_subject": "ENST",
       },
      {
        "class_subject": "EPL",
      },
      {
        "class_subject": "ESL",
      },
	  {
        "class_subject": "ETST",
      },
      {
        "class_subject": "EUPH",
      },
      {
        "class_subject": "EV",
      },
      {
        "class_subject": "FILM",
       },
      {
        "class_subject": "FINA",
      },
      {
        "class_subject": "FLUT",
      },
	  {
        "class_subject": "FNAR",
      },
      {
        "class_subject": "FREN",
      },
      {
        "class_subject": "FRHN",
      },
      {
        "class_subject": "GEOL",
       },
      {
        "class_subject": "GERM",
      },
      {
        "class_subject": "GR",
      },
	  {
        "class_subject": "GUIT",
      },
      {
        "class_subject": "HARP",
      },
      {
        "class_subject": "HARS",
      },
      {
        "class_subject": "HB",
       },
      {
        "class_subject": "HDCN",
      },
      {
        "class_subject": "HDDR",
      },
	  {
        "class_subject": "HDEV",
      },
      {
        "class_subject": "HGAM",
      },
      {
        "class_subject": "HIST",
      },
      {
        "class_subject": "HR",
       },
      {
        "class_subject": "HRTS",
      },
      {
        "class_subject": "HUMN",
      },
	  {
        "class_subject": "HS",
      },
      {
        "class_subject": "IAM",
      },
      {
        "class_subject": "INTL",
      },
      {
        "class_subject": "ITAL",
       },
      {
        "class_subject": "ITOM",
      },
      {
        "class_subject": "JAPN",
      },
	  {
        "class_subject": "JOUR",
      },
      {
        "class_subject": "JWST",
      },
      {
        "class_subject": "KNW",
      },
      {
        "class_subject": "LATN",
       },
      {
        "class_subject": "LAW",
      },
      {
        "class_subject": "MAST",
      },
	  {
        "class_subject": "MATH",
      },
      {
        "class_subject": "MDVL",
      },
      {
        "class_subject": "ME",
      },
      {
        "class_subject": "MKTG",
       },
      {
        "class_subject": "MN",
      },
      {
        "class_subject": "MNGT",
      },
	  {
        "class_subject": "MNO",
      },
      {
        "class_subject": "MPED",
      },
      {
        "class_subject": "MREP",
      },
      {
        "class_subject": "MSA",
       },
      {
        "class_subject": "MSDS",
      },
      {
        "class_subject": "MT",
      },
	  {
        "class_subject": "MUAC",
      },
      {
        "class_subject": "MUAS",
      },
      {
        "class_subject": "MUCO",
      },
      {
        "class_subject": "MUED",
       },
      {
        "class_subject": "MUHI",
      },
      {
        "class_subject": "MUPD",
      },
	  {
        "class_subject": "MURE",
      },
      {
        "class_subject": "MUTH",
      },
      {
        "class_subject": "NT",
      },
      {
        "class_subject": "OBOE",
       },
      {
        "class_subject": "ORG",
      },
      {
        "class_subject": "OT",
      },
	  {
        "class_subject": "PC",
      },
      {
        "class_subject": "PERB",
      },
      {
        "class_subject": "PERC",
      },
      {
        "class_subject": "PERE",
       },
      {
        "class_subject": "PHIL",
      },
      {
        "class_subject": "PHYS",
      },
	  {
        "class_subject": "PIAN",
      },
      {
        "class_subject": "PLSC",
      },
      {
        "class_subject": "PPIA",
      },
      {
        "class_subject": "PR",
       },
      {
        "class_subject": "PRW",
      },
      {
        "class_subject": "PS",
      },
	  {
        "class_subject": "PSYC",
      },
      {
        "class_subject": "RE",
      },
      {
        "class_subject": "RELI",
      },
      {
        "class_subject": "RMI",
       },
      {
        "class_subject": "ROTC",
      },
      {
        "class_subject": "RUSS",
      },
	  {
        "class_subject": "SAX",
      },
      {
        "class_subject": "SCCL",
      },
      {
        "class_subject": "SOCI",
      },
      {
        "class_subject": "SOSC",
       },
      {
        "class_subject": "SPAN",
      },
      {
        "class_subject": "SPRT",
      },
	  {
        "class_subject": "ST",
      },
      {
        "class_subject": "STAT",
      },
      {
        "class_subject": "STRA",
      },
      {
        "class_subject": "TC",
       },
      {
        "class_subject": "THEA",
      },
      {
        "class_subject": "TROM",
      },
	  {
        "class_subject": "TRPT",
      },
      {
        "class_subject": "TUBA",
      },
      {
        "class_subject": "UGRD",
      },
      {
        "class_subject": "UHP",
       },
      {
        "class_subject": "VIOL",
      },
      {
        "class_subject": "VLA",
      },
	  {
        "class_subject": "VOIC",
      },
      {
        "class_subject": "WGST",
      },
      {
        "class_subject": "WL",
      },
      {
        "class_subject": "WLAN",
       },
      {
        "class_subject": "WO",
      },
      {
        "class_subject": "WX",
      },
	  {
        "class_subject": "XS",
      },
      {
        "class_subject": "XX",
      },
      {
        "class_subject": "ZCET",
      },
      {
        "class_subject": "ZCIEE",
       },
      {
        "class_subject": "ZSCOPE",
      },
      {
        "class_subject": "ZIAM",
      },
	  {
        "class_subject": "ZIES",
      },
      {
        "class_subject": "ZIFE",
      },
      {
        "class_subject": "ZIFSA",
      },
      {
        "class_subject": "ZSPAN",
       },
      {
        "class_subject": "ZSWIT",
      },
    ],
  }
  return {data};

  // return {data:null};
})
.factory('Chats', function() {
  // Might use a resource here that returns a JSON array

  return {}
});
